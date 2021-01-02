<?php

namespace App\Controllers;

use App\Validators\SecretsValidator;
use CQ\Controllers\Controller;
use CQ\Crypto\Asymmetric;
use CQ\Crypto\Symmetric;
use CQ\Helpers\UUID;
use CQ\DB\DB;

class SecretsController extends Controller
{
    /**
     * List stores.
     *
     * @param object $request
     *
     * @return Json
     */
    public function listStores($request)
    {
        $stores = DB::select('stores', [
            'id',
            'name',
            'updated_at',
            'created_at',
        ], [
            'user_id' => $request->getHeader('x-api-user-id')[0],
        ]);

        return $this->respondJson(
            'Stores',
            $stores
        );
    }

    /**
     * Create store.
     *
     * @param object $request
     *
     * @return Json
     */
    public function createStore($request)
    {
        try {
            SecretsValidator::createStore($request->data);
        } catch (\Throwable $th) {
            return $this->respondJson(
                'Provided data was malformed',
                json_decode($th->getMessage()),
                422
            );
        }

        $keypair = Asymmetric::genKey();

        $data = Asymmetric::encrypt(
            json_encode($request->data->data),
            $keypair->enc['public'],
            $keypair->enc['private']
        );

        $store = [
            'id' => UUID::v6(),
            'user_id' => $request->getHeader('x-api-user-id')[0],
            'name' => $request->data->name,
            'data' => $data,
            'public_key' => $keypair->enc['public'],
        ];

        DB::create('stores', $store);

        $enc_key = base64_encode(
            Symmetric::encrypt($keypair->enc['private'])
        );

        return $this->respondJson('Store Created', [
            'id' => $store['id'],
            'key' => $enc_key,
        ]);
    }

    /**
     * Get store.
     *
     * @param object $request
     * @param string $id
     *
     * @return Json
     */
    public function getStore($request, $id)
    {
        try {
            SecretsValidator::getStore($request->data);
        } catch (\Throwable $th) {
            return $this->respondJson(
                'Provided data was malformed',
                json_decode($th->getMessage()),
                422
            );
        }

        $store = DB::get('stores', [
            'name',
            'data',
            'public_key',
        ], [
            'id' => $id,
            'user_id' => $request->getHeader('x-api-user-id')[0],
        ]);

        if (!$store) {
            return $this->respondJson('Store not found', [], 404);
        }

        try {
            $dec_key = Symmetric::decrypt(
                base64_decode($request->data->key)
            );

            $store['data'] = Asymmetric::decrypt(
                $store['data'],
                $dec_key,
                $store['public_key']
            );

            $store['data'] = json_decode($store['data']);
        } catch (\Throwable $th) {
            return $this->respondJson('Invalid key', [], 400);
        }

        return $this->respondJson($store['name'], $store['data']);
    }

    /**
     * Update store.
     *
     * @param object $request
     * @param string $id
     *
     * @return Json
     */
    public function updateStore($request, $id)
    {
        try {
            SecretsValidator::updateStore($request->data);
        } catch (\Throwable $th) {
            return $this->respondJson(
                'Provided data was malformed',
                json_decode($th->getMessage()),
                422
            );
        }

        $store = DB::get('stores', [
            'data',
            'public_key',
        ], [
            'id' => $id,
            'user_id' => $request->getHeader('x-api-user-id')[0],
        ]);

        if (!$store) {
            return $this->respondJson('Store not found', [], 404);
        }

        try {
            $dec_key = Symmetric::decrypt(
                base64_decode($request->data->key)
            );

            Asymmetric::decrypt(
                $store['data'],
                $dec_key,
                $store['public_key']
            );

            $data = Asymmetric::encrypt(
                json_encode($request->data->data),
                $store['public_key'],
                $dec_key
            );
        } catch (\Throwable $th) {
            return $this->respondJson('Invalid key', [], 400);
        }

        DB::update('stores', [
            'data' => $data,
        ], [
            'id' => $id,
        ]);

        return $this->respondJson('Store Updated');
    }

    /**
     * Delete store.
     *
     * @param object $request
     * @param string $id
     *
     * @return Json
     */
    public function deleteStore($request, $id)
    {
        DB::delete('stores', [
            'id' => $id,
            'user_id' => $request->getHeader('x-api-user-id')[0],
        ]);

        return $this->respondJson('Store Deleted');
    }
}
