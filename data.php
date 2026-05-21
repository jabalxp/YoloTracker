<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ID do arquivo do Google Drive
$fileId = '1iM6pzdI0GvtNTYUZlO5ZOZFzIXMCzd0a';
$localCacheFile = __DIR__ . '/alaba.csv';
$usingCache = false;

/**
 * Busca dados de arquivo do Google Drive usando cURL com suporte a redirecionamentos.
 */
function fetchGoogleDriveCsv($id) {
    $url = "https://docs.google.com/uc?export=download&id=" . $id;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || empty($data)) {
        return false;
    }
    
    return $data;
}

// 1. Obter os dados do CSV
$csvContent = fetchGoogleDriveCsv($fileId);

if ($csvContent !== false && strlen(trim($csvContent)) > 100) {
    file_put_contents($localCacheFile, $csvContent);
} else {
    if (file_exists($localCacheFile)) {
        $csvContent = file_get_contents($localCacheFile);
        $usingCache = true;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Falha ao buscar dados do Google Drive e nenhum cache local foi encontrado.'
        ]);
        exit;
    }
}

// 2. Parsear os dados
$tempStream = fopen('php://temp', 'r+');
fwrite($tempStream, $csvContent);
rewind($tempStream);

$headers = fgetcsv($tempStream);
if (!$headers) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados do CSV vazios ou inválidos.'
    ]);
    fclose($tempStream);
    exit;
}

$headers = array_map(function($h) {
    return trim(preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $h));
}, $headers);

$data = [];
$bestEpoch = null;
$maxMap50 = -1;
$maxMap50_95 = -1;

while (($row = fgetcsv($tempStream)) !== false) {
    if (count($row) !== count($headers) || empty($row[0])) {
        continue;
    }

    $entry = array_combine($headers, $row);
    
    foreach ($entry as $key => $val) {
        $entry[$key] = is_numeric($val) ? (float)$val : $val;
    }
    
    $data[] = $entry;

    // Determinar a melhor época baseando-se no maior mAP50(B) (e desempatando pelo mAP50-95)
    $map50Key = 'metrics/mAP50(B)';
    $map5095Key = 'metrics/mAP50-95(B)';
    
    if (isset($entry[$map50Key])) {
        $currentMap50 = $entry[$map50Key];
        $currentMap50_95 = isset($entry[$map5095Key]) ? $entry[$map5095Key] : 0;
        
        if ($currentMap50 > $maxMap50 || ($currentMap50 === $maxMap50 && $currentMap50_95 > $maxMap50_95)) {
            $maxMap50 = $currentMap50;
            $maxMap50_95 = $currentMap50_95;
            $bestEpoch = $entry;
        }
    }
}
fclose($tempStream);

if (empty($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nenhum registro de época encontrado no arquivo CSV.'
    ]);
    exit;
}

// Estatísticas agregadas
$totalEpochs = count($data);
$lastEntry = end($data);
$totalTime = isset($lastEntry['time']) ? $lastEntry['time'] : 0;
$avgTimePerEpoch = $totalEpochs > 0 ? $totalTime / $totalEpochs : 0;

$fileMtime = filemtime($localCacheFile);
$isActive = !$usingCache; 

// Formatação estruturada de séries para o Chart.js
$charts = [
    'epochs' => [],
    'losses' => [
        'train_box' => [],
        'val_box' => [],
        'train_cls' => [],
        'val_cls' => [],
        'train_dfl' => [],
        'val_dfl' => []
    ],
    'metrics' => [
        'precision' => [],
        'recall' => [],
        'mAP50' => [],
        'mAP50_95' => []
    ],
    'lr' => []
];

foreach ($data as $entry) {
    $charts['epochs'][] = (int)$entry['epoch'];
    
    $charts['losses']['train_box'][] = isset($entry['train/box_loss']) ? $entry['train/box_loss'] : null;
    $charts['losses']['val_box'][] = isset($entry['val/box_loss']) ? $entry['val/box_loss'] : null;
    $charts['losses']['train_cls'][] = isset($entry['train/cls_loss']) ? $entry['train/cls_loss'] : null;
    $charts['losses']['val_cls'][] = isset($entry['val/cls_loss']) ? $entry['val/cls_loss'] : null;
    $charts['losses']['train_dfl'][] = isset($entry['train/dfl_loss']) ? $entry['train/dfl_loss'] : null;
    $charts['losses']['val_dfl'][] = isset($entry['val/dfl_loss']) ? $entry['val/dfl_loss'] : null;
    
    $charts['metrics']['precision'][] = isset($entry['metrics/precision(B)']) ? $entry['metrics/precision(B)'] : null;
    $charts['metrics']['recall'][] = isset($entry['metrics/recall(B)']) ? $entry['metrics/recall(B)'] : null;
    $charts['metrics']['mAP50'][] = isset($entry['metrics/mAP50(B)']) ? $entry['metrics/mAP50(B)'] : null;
    $charts['metrics']['mAP50_95'][] = isset($entry['metrics/mAP50-95(B)']) ? $entry['metrics/mAP50-95(B)'] : null;
    
    $charts['lr'][] = isset($entry['lr/pg0']) ? $entry['lr/pg0'] : null;
}

echo json_encode([
    'success' => true,
    'stats' => [
        'total_epochs' => $totalEpochs,
        'total_time' => $totalTime,
        'avg_time_per_epoch' => $avgTimePerEpoch,
        'is_active' => $isActive,
        'using_cache' => $usingCache,
        'last_update' => date('d/m/Y H:i:s', $fileMtime),
        'last_update_timestamp' => $fileMtime,
        'time_since_update' => time() - $fileMtime
    ],
    'best_epoch' => $bestEpoch,
    'last_epoch' => $lastEntry,
    'charts' => $charts,
    'history' => array_reverse($data)
]);
