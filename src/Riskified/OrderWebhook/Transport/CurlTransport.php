<?php namespace Riskified\OrderWebhook\Transport;
/**
 * Copyright 2013-2014 Riskified.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://www.apache.org/licenses/LICENSE-2.0.html
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

use Riskified\OrderWebhook\Exception;
/**
 * Class CurlTransport
 * @package Riskified
 */
class CurlTransport extends AbstractTransport {

    /**
     * @var int
     */
    public $timeout = 10;
    public $dns_cache = true;

    /**
     * @param $order
     * @return mixed
     * @throws \Riskified\OrderWebhook\Exception\CurlException
     */
    protected function send_json_request($order,$options = array()) {
        $data_string = $order->toJson();
        $ch = curl_init($this->full_path());
        $options = array(
            CURLOPT_POSTFIELDS => $data_string,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->headers($data_string,$options),
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $this->user_agent,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FAILONERROR => true,
            CURLOPT_DNS_USE_GLOBAL_CACHE => $this->dns_cache
        );
        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception\CurlException(curl_error($ch), curl_errno($ch));
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $this->json_response($body, $status);
    }

    /**
     * @param $body
     * @param $status
     * @return mixed
     * @throws \Riskified\OrderWebhook\Exception\MalformedJsonException
     */
    private function json_response($body, $status) {
        $response = json_decode($body);
        if (!$response)
            throw new Exception\MalformedJsonException($body, $status);

        return $response;
    }
}
