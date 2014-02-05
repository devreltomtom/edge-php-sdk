<?php
/**
 * @file
 * Base class for Management API classes. Handles a bit of the APIClient
 * invocation, which makes the actual HTTP calls.
 *
 * @author djohnson
 */

namespace Apigee\ManagementAPI;

use Apigee\Exceptions\ResponseException;

/**
 * Base class for Management API classes. Handles a bit of the APIClient
 * invocation, which makes the actual HTTP calls.
 *
 * @author djohnson
 */
class Base extends \Apigee\Util\APIObject
{
    /**
     * Returns debug data from the last API call in a backwards-compatible way.
     *
     * @return array 
     */
    public function getDebugData()
    {
        return \Apigee\Util\DebugData::toArray();
    }

    /**
     * Reads the $attributes member of the Base subclass, and adds properly-
     * formatted members to the passed-by-reference $payload array. Note
     * that $this->attributes must be in scope (protected or public).
     *
     * @param $payload
     */
    protected function writeAttributes(&$payload)
    {
        if (property_exists($this, 'attributes') && !empty($this->attributes)) {
            $payload['attributes'] = array();
            foreach ($this->attributes as $name => $value) {
                if ($name == 'apiResourcesInfo' && is_array($value)) {
                    $value = json_encode($value);
                }
                $payload['attributes'][] = array('name' => $name, 'value' => (string)$value);
            }
        }
    }

    /**
     * Reads the response from the Management API and populates the $attributes
     * member of the Base subclass. Note that $this->attributes must be in scope
     * (protected or public).
     *
     * @param array $response
     * @param bool $return
     * @return array|void
     */
    protected function readAttributes($response, $return = FALSE)
    {
        $attributes = array();

        // We cannot use property_exists() because it ignores scope.
        // But get_object_vars only returns variables within current scope.
        $this_attributes = get_object_vars($this);
        $has_attributes = (array_key_exists('attributes', $this_attributes) && is_array($this->attributes));

        if ($has_attributes) {
            if (isset($response['attributes']) && is_array($response['attributes'])) {
                foreach ($response['attributes'] as $attrib) {
                    if (!is_array($attrib) || !array_key_exists('name', $attrib) || !array_key_exists('value', $attrib)) {
                        continue;
                    }
                    if ($attrib['name'] == 'apiResourcesInfo') {
                        $attrib['value'] = @json_decode($attrib['value'], TRUE);
                    }
                    $attributes[$attrib['name']] = $attrib['value'];
                }
            }
        }
        if ($return) {
            return $attributes;
        }
        if ($has_attributes) {
            $this->attributes = $attributes;
        }
    }
}