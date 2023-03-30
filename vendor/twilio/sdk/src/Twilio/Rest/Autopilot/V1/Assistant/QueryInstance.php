<?php

/**
 * This code was generated by
 * ___ _ _ _ _ _    _ ____    ____ ____ _    ____ ____ _  _ ____ ____ ____ ___ __   __
 *  |  | | | | |    | |  | __ |  | |__| | __ | __ |___ |\ | |___ |__/ |__|  | |  | |__/
 *  |  |_|_| | |___ | |__|    |__| |  | |    |__] |___ | \| |___ |  \ |  |  | |__| |  \
 *
 * Twilio - Autopilot
 * This is the public Twilio REST API.
 *
 * NOTE: This class is auto generated by OpenAPI Generator.
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */


namespace Twilio\Rest\Autopilot\V1\Assistant;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceResource;
use Twilio\Options;
use Twilio\Values;
use Twilio\Version;
use Twilio\Deserialize;


/**
 * @property string|null $accountSid
 * @property \DateTime|null $dateCreated
 * @property \DateTime|null $dateUpdated
 * @property array|null $results
 * @property string|null $language
 * @property string|null $modelBuildSid
 * @property string|null $query
 * @property string|null $sampleSid
 * @property string|null $assistantSid
 * @property string|null $sid
 * @property string|null $status
 * @property string|null $url
 * @property string|null $sourceChannel
 * @property string|null $dialogueSid
 */
class QueryInstance extends InstanceResource
{
    /**
     * Initialize the QueryInstance
     *
     * @param Version $version Version that contains the resource
     * @param mixed[] $payload The response payload
     * @param string $assistantSid The SID of the [Assistant](https://www.twilio.com/docs/autopilot/api/assistant) that is the parent of the new resource.
     * @param string $sid The Twilio-provided string that uniquely identifies the Query resource to delete.
     */
    public function __construct(Version $version, array $payload, string $assistantSid, string $sid = null)
    {
        parent::__construct($version);

        // Marshaled Properties
        $this->properties = [
            'accountSid' => Values::array_get($payload, 'account_sid'),
            'dateCreated' => Deserialize::dateTime(Values::array_get($payload, 'date_created')),
            'dateUpdated' => Deserialize::dateTime(Values::array_get($payload, 'date_updated')),
            'results' => Values::array_get($payload, 'results'),
            'language' => Values::array_get($payload, 'language'),
            'modelBuildSid' => Values::array_get($payload, 'model_build_sid'),
            'query' => Values::array_get($payload, 'query'),
            'sampleSid' => Values::array_get($payload, 'sample_sid'),
            'assistantSid' => Values::array_get($payload, 'assistant_sid'),
            'sid' => Values::array_get($payload, 'sid'),
            'status' => Values::array_get($payload, 'status'),
            'url' => Values::array_get($payload, 'url'),
            'sourceChannel' => Values::array_get($payload, 'source_channel'),
            'dialogueSid' => Values::array_get($payload, 'dialogue_sid'),
        ];

        $this->solution = ['assistantSid' => $assistantSid, 'sid' => $sid ?: $this->properties['sid'], ];
    }

    /**
     * Generate an instance context for the instance, the context is capable of
     * performing various actions.  All instance actions are proxied to the context
     *
     * @return QueryContext Context for this QueryInstance
     */
    protected function proxy(): QueryContext
    {
        if (!$this->context) {
            $this->context = new QueryContext(
                $this->version,
                $this->solution['assistantSid'],
                $this->solution['sid']
            );
        }

        return $this->context;
    }

    /**
     * Delete the QueryInstance
     *
     * @return bool True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete(): bool
    {

        return $this->proxy()->delete();
    }

    /**
     * Fetch the QueryInstance
     *
     * @return QueryInstance Fetched QueryInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch(): QueryInstance
    {

        return $this->proxy()->fetch();
    }

    /**
     * Update the QueryInstance
     *
     * @param array|Options $options Optional Arguments
     * @return QueryInstance Updated QueryInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update(array $options = []): QueryInstance
    {

        return $this->proxy()->update($options);
    }

    /**
     * Magic getter to access properties
     *
     * @param string $name Property to access
     * @return mixed The requested property
     * @throws TwilioException For unknown properties
     */
    public function __get(string $name)
    {
        if (\array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        if (\property_exists($this, '_' . $name)) {
            $method = 'get' . \ucfirst($name);
            return $this->$method();
        }

        throw new TwilioException('Unknown property: ' . $name);
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString(): string
    {
        $context = [];
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Autopilot.V1.QueryInstance ' . \implode(' ', $context) . ']';
    }
}

