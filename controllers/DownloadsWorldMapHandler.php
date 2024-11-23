<?php
namespace APP\plugins\generic\downloadsWorldMap\controllers;

use APP\handler\Handler;
use APP\core\Services;
use APP\statistics\StatisticsHelper;
class DownloadsWorldMapHandler extends Handler
{
function downloadsPerCountry($args, $request)
{
    $bookId = $args[0] ?? null;

    if(!$bookId){
        http_response_code(404);
        echo json_encode(["error"=>'The book parameter is required']);
        return;
    }
    try {
         $statsService = Services::get('geoStats');
    
        $allowedParams = [
            'contextIds' => [$request->getContext()->getId()],
            'submissionIds' => [$bookId],
            'orderDirection' => StatisticsHelper::STATISTICS_ORDER_DESC,
        
        ];

        $totals = $statsService->getTotals($allowedParams, StatisticsHelper::STATISTICS_DIMENSION_COUNTRY);
     
        $response = [];
        foreach ($totals as $total) {
            $countryCode = $total->country ?? 'Unknown';
            $metric = $total->metric;
            $response[$countryCode] = ($response[$countryCode] ?? 0) + $metric;

         
        }

        http_response_code(200); 
        header('Content-Type: application/json');
        echo json_encode($response);

    } catch (\Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to fetch download statistics.',
            'details' => $e->getMessage(),
        ]);
    }
}
}