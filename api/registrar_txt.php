<?php
$config = [
    'supabase_url' => 'https://bzbiqiiahovgowadcbrw.supabase.co',
    'supabase_key' => 'SUA_CHAVE_SERVICE_ROLE_AQUI'
];

$data = json_decode(file_get_contents('php://input'), true);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $config['supabase_url'].'/rest/v1/entradas_txt',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'apikey: '.$config['supabase_key'],
        'Authorization: Bearer '.$config['supabase_key'],
        'Content-Type: application/json',
        'Prefer: return=representation'
    ],
    CURLOPT_POSTFIELDS => json_encode(['dados'=>json_encode($data['dados'])])
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($httpcode>=200 && $httpcode<300) echo json_encode(['success'=>true]);
else echo json_encode(['success'=>false,'message'=>$response]);
