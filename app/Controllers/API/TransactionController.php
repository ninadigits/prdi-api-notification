<?php

namespace App\Controllers\API;

use App\Models\Notification;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;

class TransactionController extends ResourceController
{
    function __construct()
    {
        helper(['custom']);
    }

    function index()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'GET')
        {
            return $this->_view();
        } else {
            $res = [
                'status'    => false,
                'code'      => ResponseInterface::HTTP_METHOD_NOT_ALLOWED,
                'message'   => "Method Not Allowed",
            ];

            return $this->respond($res, $res['code']);
        }
    }
    
    function paid()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'POST')
        {
            $sendToPRDI = $this->_send('PAID');

            return $sendToPRDI;

            // var_dump($sendToPRDI);
        } else {
            $res = [
                'status'    => false,
                'code'      => ResponseInterface::HTTP_METHOD_NOT_ALLOWED,
                'message'   => "Method Not Allowed",
            ];

            return $this->respond($res, $res['code']);
        }
    }

    function finish()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'POST')
        {
            $sendToPRDI = $this->_send('FINISH');

            return $sendToPRDI;

            // var_dump($sendToPRDI);
        } else {
            $res = [
                'status'    => false,
                'code'      => ResponseInterface::HTTP_METHOD_NOT_ALLOWED,
                'message'   => "Method Not Allowed",
            ];

            return $this->respond($res, $res['code']);
        }
    }
    
    private function _send($orderStatus)
    {
        $benchmark = Services::timer();
        $benchmark->start('api_process');
        try {
            $notification = new Notification();
            $notificationRegData = json_decode($notification->get_notification_reg());

            $benchmark->stop('api_process');

            if ($notificationRegData) {
                if (count($notificationRegData->RESPONSE1) == 0) {
                    $res = [
                        'status'        => FALSE,
                        'code'          => ResponseInterface::HTTP_NOT_FOUND,
                        'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                        'message'       => "Data Not Found",
                        'data'          => NULL,
                    ];

                    return $this->respond($res, $res['code']);
                }

                if ($orderStatus == 'PAID') {
                    $compileNotificationData = $notification->compile_notification_reg_paid($notificationRegData);
                } elseif ($orderStatus == 'FINISH') {
                    $compileNotificationData = $notification->compile_notification_reg_finish($notificationRegData);
                }

                if(isset($compileNotificationData['RESPONSE1'][0]['CEK_STATUS'])) {
                    if($compileNotificationData['RESPONSE1'][0]['CEK_STATUS'] == 'FAILED') {
                        $res = [
                            'status'        => FALSE,
                            'code'          => ResponseInterface::HTTP_BAD_REQUEST,
                            'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                            'message'       => $compileNotificationData['RESPONSE1'][0]['MESSAGE'] . " ({$compileNotificationData['RESPONSE1'][0]['STATUS_CODE']})",
                        ];
    
                        return $this->respond($res, $res['code']);
                    }
                }

                $res = [
                    'status'        => TRUE,
                    'code'          => ResponseInterface::HTTP_OK,
                    'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                    'message'       => 'Data Successfuly Sended',
                ];

            } else {
                $res = [
                    'status'        => FALSE,
                    'code'          => ResponseInterface::HTTP_BAD_REQUEST,
                    'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                    'message'       => $notificationRegData->RESPONSE1[0]->MESSAGE . " ({$notificationRegData->RESPONSE1[0]->STATUS_CODE})",
                ];
            }

            return $this->respond($res, $res['code']);
        } catch (\Exception $e) {
            $benchmark->stop('api_process');
            
            $res = [
                'status'        => FALSE,
                'code'          => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
                'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                'message'       => getenv('CI_ENVIRONMENT') === 'development' ? $e->getMessage() : 'an error occurred while retrieving data',
            ];

            return $this->respond($res, $res['code']);
        }
    
        
    }

    private function _view()
    {
        $benchmark = Services::timer();
        $benchmark->start('api_process');
        try {
            $notification = new Notification();
            $notificationRegData = json_decode($notification->get_notification_reg());

            $benchmark->stop('api_process');

            if ($notificationRegData) {
                if (count($notificationRegData->RESPONSE1) == 0) {
                    $res = [
                        'status'        => FALSE,
                        'code'          => ResponseInterface::HTTP_NOT_FOUND,
                        'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                        'message'       => "Data Not Found",
                        'data'          => NULL,
                    ];
                } else {
                    $res = [
                        'status'        => TRUE,
                        'code'          => ResponseInterface::HTTP_OK,
                        'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                        'message'       => "success",
                        'data'          => $notificationRegData->RESPONSE1,
                    ];
                }
            } else {
                $res = [
                    'status'        => FALSE,
                    'code'          => ResponseInterface::HTTP_BAD_REQUEST,
                    'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                    'message'       => $notificationRegData->RESPONSE1[0]->MESSAGE . " ({$notificationRegData->RESPONSE1[0]->STATUS_CODE})",
                    'data'          => NULL,
                ];
            }

            return $this->respond($res, $res['code']);

        } catch (\Exception $e) {
            $benchmark->stop('api_process');
        
            $res = [
                'status'        => FALSE,
                'code'          => ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
                'responseTime'  => $benchmark->getElapsedTime('api_process') . ' (s)',
                'message'       => getenv('CI_ENVIRONMENT') === 'development' ? $e->getMessage() : 'an error occurred while retrieving data',
                'data'          => NULL,
            ];

            return $this->respond($res, $res['code']);
        }
    
        
    }
}
