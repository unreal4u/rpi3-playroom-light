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

class ReadButtonSensor extends Base {
    /**
     * @var GPIO
     */
    private $gpio;

    /**
     * @var InputPin
     */
    private $buttonPin;

    /**
     * @var string
     */
    private $stateFileLocation;

    /**
     * Will be executed once before running the actual job
     *
     * @return JobContract
     */
    public function setUp(): JobContract
    {
	$this->stateFileLocation = __DIR__ . '/../state/current.state';
        $this->gpio = new GPIO();
        // Retrieve pin 21 and configure it as an input pin
        $this->buttonPin = $this->gpio->getInputPin(24);

        // Configure interrupts for both rising and falling edges
        $this->buttonPin->setEdge(InputPinInterface::EDGE_BOTH);

        return $this;
    }

    public function configure()
    {
        $this
            ->setName('playroom:button')
            ->setDescription('Reads out the light button switch and writes this back to a file')
            ->setHelp('TODO')
        ;
    }

    /**
     * Runs the actual job that needs to be executed
     *
     * @return bool Returns true if job was successful, false otherwise
     */
    public function runJob(): bool
    {
        // Create an interrupt watcher
        $interruptWatcher = $this->gpio->createWatcher();

        // Register a callback to be triggered on pin interrupts
        $interruptWatcher->register($this->buttonPin, function (InputPinInterface $pin, $value) {
            $this->logger->debug('Got a value from the sensor', [
                'pinNumber' => $pin->getNumber(),
                'value' => $value,
                'uniqueIdentifier' => $this->getUniqueIdentifier(),
            ]);

            if ($value === 1) {
		$this->logger->info('Detected an event', ['uniqueIdentifier' => $this->getUniqueIdentifier()]);
		file_put_contents($this->stateFileLocation, 'toggle');
		// This button seems to interfere quite a bit when the button is actually pressed
		// This usleep will ignore any other signals received in 500ms
		usleep(500000);
            }

            // Returning false will make the watcher return false immediately
            return true;
        });

        /** @noinspection PhpStatementHasEmptyBodyInspection */
        while ($interruptWatcher->watch($this->forceKillAfterSeconds()));

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
        return 3600;
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
