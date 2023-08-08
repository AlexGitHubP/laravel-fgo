# laravel-fgo

Boilerplate class for integration with FGO API


Call example from random controller:

Include use App\Models\FgoApi; in controller

# instantiate FGO

$orderDetails = [
    'currency' => 'RON',
    'clientCompany' => 'Companie test',
    'clientRegistruComert' => 'J40/123/2023',
    'clientCUI' => 'RO123123123',
    'clientCounty' => 'Alba',
    'clientCity' => 'Alba',
    'clientAddress' => 'Eroilor, nr. 3',
    'clientEmail' => 'test@yahoo.com',
    'clientPhone' => '0909090909',
    
];

$fgoInstance = new FgoApi($orderDetails);

# generate FGO invoice and return response

$invoice = $fgoInstance->generateInvoice($products);
