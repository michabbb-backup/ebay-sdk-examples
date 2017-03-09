<?php
/**
 * Copyright 2017 David T. Sadler
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Include the SDK by using the autoloader from Composer.
 */
require __DIR__.'/../vendor/autoload.php';

/**
 * Include the configuration values.
 *
 * Ensure that you have edited the configuration.php file
 * to include your application keys.
 */
$config = require __DIR__.'/../configuration.php';

/**
 * The namespaces provided by the SDK.
 */
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Product\Services;
use \DTS\eBaySDK\Product\Types;
use \DTS\eBaySDK\Product\Enums;

/**
 * Create the service object.
 */
$service = new Services\ProductService([
    'credentials' => $config['production']['credentials'],
    'globalId'    => Constants\GlobalIds::MOTORS
]);

/**
 * Create the request object.
 */
$request = new Types\FindProductsRequest();

$productSearch = new Types\ProductRequest();
// Required field.
$productSearch->invocationId = '123';uniqid();
$productSearch->categoryId = '33567';
$productSearch->keywords = 'brake pads';
/**
 * Limit the results to 10 items per page.
 */
$productSearch->paginationInput = new Types\PaginationInput();
$productSearch->paginationInput->entriesPerPage = 10;

$request->productSearch = [$productSearch];

$pageNum = 1;

do {
    $request->productSearch[0]->paginationInput->pageNumber = $pageNum;

    /**
     * Send the request.
     */
    $response = $service->findProducts($request);

    /**
     * Output the result of calling the service operation.
     */
    echo "\n\n==================\nResults for page $pageNum\n==================\n";

    if (isset($response->errorMessage)) {
        foreach ($response->errorMessage->error as $error) {
            printf(
                "%s: %s\n\n",
                $error->severity=== Enums\ErrorSeverity::C_ERROR ? 'Error' : 'Warning',
                $error->message
            );
        }
    }

    if ($response->ack !== 'Failure') {
        foreach ($response->productSearchResult as $result) {
            foreach ($result->products as $product) {
                echo "\n\n==================\nProduct\n==================\n";
                if(isset($product->productIdentifier)) {
                    printf("ePID %s\n", $product->productIdentifier->ePID);
                    printf("Product ID %s\n", $product->productIdentifier->productId);
                }
                foreach($product->productDetails as $details) {
                    printf("Property Name \n%s :", $details->propertyName);
                    foreach($details->value as $value) {
                        printf(
                            "%s %s %s ",
                            isset($value->number) ? $value->number->value : '',
                            isset($value->text) ? $value->text->value : '',
                            isset($value->URL) ? $value->URL->value : ''
                        );
                    }
                }
            }
        }
    }

    $pageNum += 1;

} while (isset($response->productSearchResult) && count($response->productSearchResult) && $pageNum <= $response->productSearchResult[0]->paginationOutput->totalPages);


$ePID = '75906122';:tabn
