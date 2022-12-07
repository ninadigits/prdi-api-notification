<?php
if (!function_exists('var_dump_ret')) {
    function var_dump_ret($mixed = null) {
        ob_start();
        var_dump($mixed);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}

if (!function_exists('json_decode_check')) {
    function json_decode_check($json) {
        $ret = FALSE;
        $json_decode = json_decode($json);

        if (json_last_error() === JSON_ERROR_NONE) {
            $ret = $json_decode;
        }
        
        return $ret;
    }
}

if (!function_exists('fcm_response_check')) {
    function fcm_response_check($fcm_response) {
        $resp = json_decode($fcm_response, TRUE);

        if (array_key_exists('failure', $resp) AND $resp['failure'] >= 1) {
            $failure = TRUE;
            $message = $resp['results'][0]['error'];
        }
        else {
            $failure = FALSE;
            $message = $resp;
        }
        
        $ret['is_failed'] = $failure;
        $ret['message'] = $message;
        
        return $ret;
    }
}

if (!function_exists('response_check')) {
    function response_check($response) {
        $decoded = json_decode(str_replace(array("\r", "\n", "\t"), '', $response), TRUE);

        // var_dump($decoded); die;
        if (json_last_error() === 0) {
            if (count($decoded) > 0) {
                $status = FALSE;
                $message = $decoded;
                $data = NULL;
            }
            else {
                $status = FALSE;
                $message = 'No data found.';
                $data = NULL;
            }
        }
        else {
            $status = FALSE;
            $message = 'PHP json_decode() error: Code '.json_last_error().': "'.json_last_error_msg().'"';
            $data = $response;
        }

        return $message;
    }
}

if (!function_exists('response_check_single')) {
    function response_check_single($response) {
        $decoded = json_decode(str_replace(array("\r", "\n", "\t"), '', $response), TRUE);
        
        return $decoded;
    }
}

if(!function_exists('send_request')) {
    function send_request($param, $url, $method = 'POST') {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS =>json_encode($param),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            )
        ));

        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
}