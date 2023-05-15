<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Monitor\V1;

use Twilio\InstanceContext;
use Twilio\Values;
use Twilio\Version;

class AlertContext extends InstanceContext {
    /**
     * Initialize the AlertContext
     * 
     * @param \Twilio\Version $version Version that contains the resource
     * @param string $sid The sid
     * @return \Twilio\Rest\Monitor\V1\AlertContext 
     */
    public function __construct(Version $version, $sid) {
        parent::__construct($version);

        // Path Solution
        $this->solution = array('sid' => $sid,);

        $this->uri = '/Alerts/' . rawurlencode($sid) . '';
    }

    /**
     * Fetch a AlertInstance
     * 
     * @return AlertInstance Fetched AlertInstance
     */
    public function fetch() {
        $params = Values::of(array());

        $payload = $this->version->fetch(
            'GET',
            $this->uri,
            $params
        );

        return new AlertInstance($this->version, $payload, $this->solution['sid']);
    }

    /**
     * Deletes the AlertInstance
     * 
     * @return boolean True if delete succeeds, false otherwise
     */
    public function delete() {
        return $this->version->delete('delete', $this->uri);
    }

    /**
     * Provide a friendly representation
     * 
     * @return string Machine friendly representation
     */
    public function __toString() {
        $context = array();
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Monitor.V1.AlertContext ' . implode(' ', $context) . ']';
    }
}