<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Paddle\SDK\Client;
use Paddle\SDK\Entities\Shared\CatalogType;
use Paddle\SDK\Entities\Shared\CurrencyCode;
use Paddle\SDK\Entities\Shared\CustomData;
use Paddle\SDK\Entities\Shared\Interval;
use Paddle\SDK\Entities\Shared\Money;
use Paddle\SDK\Entities\Shared\TaxCategory;
use Paddle\SDK\Entities\Shared\TimePeriod;
use Paddle\SDK\Resources\Customers\Operations\CreateCustomer;
use Paddle\SDK\Resources\Prices\Operations\CreatePrice;
use Paddle\SDK\Resources\Products\Operations\CreateProduct;

class PaddleController extends Controller
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function createProducts()
    {
        $product = $this->client->products->create(
            new CreateProduct(
                'Test Product',
                TaxCategory::DigitalGoods(),
                CatalogType::Standard(),
                'Test Product Description',
            )
        );

        $priceOne = $this->client->prices->create(
            new CreatePrice(
                description: 'Test Price 1 Description',
                productId: $product->id,
                unitPrice: new Money('10.00', CurrencyCode::USD()),
                name: 'Price One',
                type: CatalogType::Standard(),
                billingCycle: new TimePeriod(
                    interval: Interval::Month(),
                    frequency: 1
                )
            )
        );

        $customerOne = $this->client->customers->create(
            new CreateCustomer(
                email: '5xNl0@example.com',
                name: 'Test Customer 1',
                customData: new CustomData([
                    'meta_one' => 'value_one',
                    'meta_two' => 'value_two',
                ])
            )
        );
    }
}
