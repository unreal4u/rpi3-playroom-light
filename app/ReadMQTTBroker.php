<?php

declare(strict_types=1);

namespace unreal4u\rpiMagneticSwitch;

use unreal4u\rpiCommonLibrary\Base;
use unreal4u\rpiCommonLibrary\JobContract;
use unreal4u\MQTT\DataTypes\Message;
use unreal4u\MQTT\DataTypes\TopicFilter;

class ReadMQTTBroker extends Base {
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

        return $this;
    }

    public function configure()
    {
        $this
            ->setName('playroom:mqtt-listener')
            ->setDescription('Subscribes to MQTT and writes incoming commands to a file')
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
        $mqttCommunicator = $this->communicationsFactory('MQTT');
        $topicFilter = new TopicFilter('commands/playroom/light');
        $mqttCommunicator->subscribeToTopic($topicFilter, function(Message $message) {
            file_put_contents($this->stateFileLocation, $message->getPayload());
        });
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
