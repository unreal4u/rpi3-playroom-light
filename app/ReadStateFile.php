<?php

declare(strict_types=1);

namespace unreal4u\rpiMagneticSwitch;

use PiPHP\GPIO\GPIO;
use PiPHP\GPIO\Pin\InputPin;
use PiPHP\GPIO\Pin\InputPinInterface;
use PiPHP\GPIO\Pin\OutputPin;
use PiPHP\GPIO\Pin\PinInterface;
use unreal4u\rpiCommonLibrary\Base;
use unreal4u\rpiCommonLibrary\JobContract;

class ReadStateFile extends Base {
    /**
     * FALSE: lamp is off, TRUE: lamp is on
     * @var bool
     */
    private $currentState = false;

    /**
     * OutputPin
     */
    private $relayPin;

    /**
     * @var string
     */
    private $stateFileLocation;

    /**
     * @var int
     */
    private $lastStateFileModification;

    /**
     * Will be executed once before running the actual job
     *
     * @return JobContract
     */
    public function setUp(): JobContract
    {
	$this->stateFileLocation = __DIR__ . '/../state/current.state';
        if (!file_exists($this->stateFileLocation)) {
            file_put_contents($this->stateFileLocation, 'off');
        }
        $this->gpio = new GPIO();
        // Also retrieve pin 15 and configure it as an output pin
        $this->relayPin = $this->gpio->getOutputPin(15);
        $this->setRelayPin();
        $this->logger->debug('Everything set up, ready to start execution');

        return $this;
    }

    public function configure()
    {
        $this
            ->setName('playroom:read-state-file')
            ->setDescription('Turns the light off or on depending on the state of the statefile')
            ->setHelp('Reads out the state file and turns the light on or off, notifying the broker as well')
        ;
    }

    private function combineStateFileAndCurrentStatus(): bool
    {
        $fileContents = file_get_contents($this->stateFileLocation);
	switch (trim($fileContents)) {
	    case 'on':
                $this->currentState = true;
                break;
            case 'toggle':
                $this->currentState = !$this->currentState;
                break;
            case 'off':
            default:
                $this->currentState = false;
                break;
	}
        $this->logger->debug('Finished reading state file', ['veredict' => $this->currentState]);

        return $this->currentState;
    }

    private function setRelayPin(): self
    {
        $mqttCommunicator = $this->communicationsFactory('MQTT');
        $this->combineStateFileAndCurrentStatus();
        if ($this->currentState === true) {
            $this->relayPin->setValue(PinInterface::VALUE_HIGH);
            $this->logger->debug('Sent command to relay');
            $mqttCommunicator->sendMessage('status/playroom/light', 'on');
        } else {
            $this->relayPin->setValue(PinInterface::VALUE_LOW);
            $this->logger->debug('Sent command to relay');
            $mqttCommunicator->sendMessage('status/playroom/light', 'off');
        }

        return $this;
    }

    /**
     * Runs the actual job that needs to be executed
     *
     * @return bool Returns true if job was successful, false otherwise
     */
    public function runJob(): bool
    {
        $run = true;
        while ($run === true) {
            $statInformation = stat($this->stateFileLocation);
            if ($statInformation['mtime'] > $this->lastStateFileModification) {
		$this->setRelayPin();
                $this->lastStateFileModification = $statInformation['mtime'];
            }
            usleep(100000);
	    clearstatcache();
        }
        return true;
    }

    /**
     * If method runJob returns false, this will return an array with errors that may have happened during execution
     *
     * @return \Generator
     */
    public function retrieveErrors(): \Generator
    {
        yield '';
    }

    /**
     * The number of seconds after which this script should kill itself
     *
     * @return int
     */
    public function forceKillAfterSeconds(): int
    {
        return 1;
    }

    /**
     * The loop should run after this amount of microseconds (1 second === 1000000 microseconds)
     *
     * @return int
     */
    public function executeEveryMicroseconds(): int
    {
        return 0;
    }
}
