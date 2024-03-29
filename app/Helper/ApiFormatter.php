<?php

namespace App\Helper;

class ApiFormatter
{
    protected static $success_response =  [
        'code' =>  null,
        'data' => null,
        'message' => null,
    ];

    protected static $error_response =  [
        'code' =>  null,
        'errors' => null,
        'message' => null,
    ];

    public static function pagination($message, $data)
    {
        $response['status'] = 200;
        $response['message'] = $message;
        $response = array_merge_recursive($response, $data->toArray());

        return response()->json($response, 200);
    }

    public static function success($code, $message, $data = null)
    {
        if($code === 200 || $code === 201){
            self::$success_response['code'] = $code;
            self::$success_response['data'] = $data;
            self::$success_response['message'] = $message;
            return response()->json(self::$success_response, self::$success_response['code']);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Wrong status code'
        ], 400);
    }

    public static function error($code, $message)
    {
        if($code !== 200 || $code !== 201){
            self::$error_response['code'] = $code;
            self::$error_response['errors'] = [
                'message' => '',
                'field' => '',
            ];
            self::$error_response['message'] = $message;

            return response()->json(self::$error_response, self::$error_response['code']);
        }

        return response()->json([
            'code' => 400,
            'message' => 'Wrong status code'
        ], 400);
    }

    public static function validate($errors)
    {
        $errors = json_decode($errors);
        $array = [];

        //format error validation message laravel to Wowrack RESTAPI format
        foreach($errors as $key => $item){
            foreach($item as $error){
                $array[] = [
                    'message' => $error,
                    'field' => $key,
                ];
            }
        }

        return response()->json([
            'code' => 400,
            'errors' => $array,
            'message' => 'Input validation error'
        ], 400);
    }
}
