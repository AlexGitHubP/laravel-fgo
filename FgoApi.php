<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use InvalidArgumentException;

class FgoApi extends Model{
    
    const CUI      = '';
    const SECRET   = '';
    const BASE_URL = 'https://testapp.fgo.ro/';

    private $currency;
    private $clientCompany;
    private $clientRegistruComert;
    private $clientCUI;
    private $clientCounty;
    private $clientCity;
    private $clientAddress;
    private $clientEmail;
    private $clientPhone;

    private $hash;

    public function __construct($params){
        try {
            $this->buildParams($params);
        } catch (\InvalidArgumentException $e) {
            dd($e);
            //return $e->getMessage();
        }
        
        $this->generateHash();
    }

    private function buildParams($params){
        if(isset($params) && !empty($params) && count($params) > 0){
            foreach ($params as $key => $param) {
                if (property_exists($this, $key)) {
                    $this->$key = $param;
                } else {
                    throw new InvalidArgumentException("Property '$key' does not exist.");
                }
                
            }
        }
    }

    private function generateHash(){

        $cui    = FgoApi::CUI;
        $secret = FgoApi::SECRET;
        $client = $this->clientCompany;
        $hash   = strtoupper(SHA1($cui.$secret.$client));

        $this->hash = $hash;
    }

    public function getCurrentTime(){
        return Carbon::now()->format('Y-m-d H:i:s');
    }

    public function getHash(){
        return $this->hash;
    }

    static function buildProducts($products){
        echo '<pre>';print_r($products);exit;
        $arr = array();
        foreach ($products as $key => $product) {
            $arr = array(
                'Continut['.$key.'][Denumire]' => 'Produs 1',
                'Continut['.$key.'][PretUnitar]' => '10',  
                'Continut['.$key.'][UM]' => 'buc',
                'Continut['.$key.'][NrProduse]' => '1',
                'Continut['.$key.'][CotaTVA]' => '19'
            );
        }
        
    }

    public function generateInvoice($products){
        $produtcs = self::buildProducts($products);    

        $endpoint = self::BASE_URL.'publicws/factura/emitere';
        $data = array(
                    'CodUnic' => self::CUI,
                    'Hash' => $this->getHash(),
                    'Client[Denumire]' => $this->clientCompany,
                    'Client[CodUnic]' => '12424870',
                    'Client[Tip]' => 'PJ',
                    'Client[NrRegCom]' => '',
                    'Client[Judet]' => '',
                    'Text' => 'Nume Delegat',
                    'Explicatii' => 'Explicatii factura',
                    'Valuta' => 'RON',
                    'Serie' => 'ACT',
                    'TipFactura' => 'Factura',
                    'PlatformaUrl' => self::BASE_URL,
                    $products
                );

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $response = file_get_contents($endpoint, false, $context);
        $response = json_decode($response);
        if($response->Success==true){
            return $response->Factura;
        }

        echo '<pre>';print_r(json_decode($response));exit;
    }

    public function storeInvoice($invoice){

        $invoiceID = DB::table('orders_invoices')->insertGetId([
            'invoice_number' => $invoice->Numar,
            'invoice_series' => $invoice->Serie,
            'invoice_link'   => $invoice->Link,
            'status'         => 'fgo_sent',
            'created_at'     => $this->getCurrentTime(),
            'updated_at'     => $this->getCurrentTime(),
        ]);

        return $invoiceID;

    }

}
