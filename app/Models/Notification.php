<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class Notification extends Model
{
    function get_notification_reg() {
        $param = [];
        $url = getenv('api.getNotif');
        $response = send_request($param, $url);
        return $response;
    }

    function update_notification_reg($data) {
        $param = [
            "P1" => $data['PRDI_ID'],
            "P2" => $data['NOTIF_MESSAGE'],
            "P3" => $data['STATUS'],
            "P4" => $data['DATA_ID'],
            "P5" => $data['NOTIF_TYPE_ID'],
            "P6" => $data['SENT_DATE']
        ];
        $url = getenv('api.getNotif');
        $response = send_request($param, $url);
        return $response;
    }

    function compile_notification_reg_paid($notificationData) {
        foreach ($notificationData->RESPONSE1 as $key => $res) :
            $url = getenv('api.Zuhlke');
            
            $data = [
                "orderId" => $res->BOOKING_ORDER,
                "orderStatus" => $res->STATUS_PAYMENT,
                "outletId" => $res->OUTLET_ID,
                "paymentAmount" => $res->JUMLAH_PAYMENT,
                "labRegistrationNumber" => $res->NO_LAB,
                "offlinePaymentCompleted" => true,
                "billNumber" => null,
                "labResultAvailable" => true,
                "timestamp" => date('Y-m-d\TH:i:s\Z', strtotime($res->VALIDATE_TIMESTAMP)),
            ];
            
            $request_header = [
                'Content-Type'      => 'application/json',
                'accept'            => '*/*',
                'Accept-Language'   => 'en',
            ];

            if ($res->STATUS == 'PAID') {
                $client = Services::curlrequest();
                $response = $client->request('PATCH', $url, ['json' => $data, 'headers' => $request_header, 'http_errors' => false, 'debug' => true]);
                // $res = $response->getBody();
                $decode = json_decode($response->getBody(), true);
                // echo json_encode($decode); die;

                if (isset($decode['apiError'])) {
                    $updateData = [
                        'PRDI_ID'           => '',
                        'NOTIF_MESSAGE'     => "{$decode['apiError']['errorCode']}: {$decode['apiError']['errorMessage']}",
                        'STATUS'            => 'FAIL',
                        'DATA_ID'           => $res->BOOKING_ORDER,
                        'NOTIF_TYPE_ID'     => '02',
                        'SENT_DATE'         => date('d-m-Y H:i:s'),
                    ];
                } else {
                    $updateData = [
                        'PRDI_ID'           => $decode['id'],
                        'NOTIF_MESSAGE'     => '',
                        'STATUS'            => 'SENT',
                        'DATA_ID'           => $res->BOOKING_ORDER,
                        'NOTIF_TYPE_ID'     => '02',
                        'SENT_DATE'         => date('d-m-Y H:i:s'),
                    ];
                }

                $updateNotificationReg = json_decode($this->update_notification_reg($updateData), TRUE);

                if(isset($updateNotificationReg['RESPONSE1'][0]['CEK_STATUS']) == 'FAILED') {
                    return $updateNotificationReg;
                }
            }
        endforeach;
    }

    function compile_notification_reg_finish($notificationData) {
        $url = getenv('api.Zuhlke');
        foreach ($notificationData->RESPONSE1 as $key => $res) :
            $data = [
                "orderId" => $res->BOOKING_ORDER,
                "orderStatus" => $res->STATUS_PAYMENT,
                "outletId" => $res->OUTLET_ID,
                "paymentAmount" => $res->JUMLAH_PAYMENT,
                "labRegistrationNumber" => $res->NO_LAB,
                "offlinePaymentCompleted" => true,
                "billNumber" => $res->NO_NOTA,
                "labResultAvailable" => true,
                "timestamp" => date('Y-m-d\TH:i:s\Z', strtotime($res->FINISH_TIMESTAMP)),
            ];

            $request_header = [
                'Content-Type'      => 'application/json',
                'accept'            => '*/*',
                'Accept-Language'   => 'en',
            ];

            if ($res->STATUS == 'FINISH') {
                $client = Services::curlrequest();
                $response = $client->request('PATCH', $url, ['json' => $data, 'headers' => $request_header, 'http_errors' => false, 'debug' => true]);
                $decode = json_decode($response->getBody(), true);

                // echo json_encode($decode); die;

                if (isset($decode['apiError'])) {
                    $updateData = [
                        'PRDI_ID'           => '',
                        'NOTIF_MESSAGE'     => "{$decode['apiError']['errorCode']}: {$decode['apiError']['errorMessage']}",
                        'STATUS'            => 'FAIL',
                        'DATA_ID'           => $res->BOOKING_ORDER,
                        'NOTIF_TYPE_ID'     => '01',
                        'SENT_DATE'         => date('d-m-Y H:i:s'),
                    ];
                } else {
                    $updateData = [
                        'PRDI_ID'           => $decode['id'],
                        'NOTIF_MESSAGE'     => '',
                        'STATUS'            => 'SENT',
                        'DATA_ID'           => $res->BOOKING_ORDER,
                        'NOTIF_TYPE_ID'     => '01',
                        'SENT_DATE'         => date('d-m-Y H:i:s'),
                    ];
                }

                $updateNotificationReg = json_decode($this->update_notification_reg($updateData), TRUE);

                if(isset($updateNotificationReg['RESPONSE1'][0]['CEK_STATUS']) == 'FAILED') {
                    return $updateNotificationReg;
                }
            }
        endforeach;
    }
}
