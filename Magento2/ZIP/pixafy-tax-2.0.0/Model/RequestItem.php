<?php
/**
 * Created by PhpStorm.
 * User: EETIENNE
 * Date: 9/9/2016
 * Time: 12:58 PM
 */

namespace VertexSMB\Tax\Model;

class RequestItem extends \Magento\Framework\Model\AbstractModel
{


    protected $vertexHelper;

    public function __construct(

                \VertexSMB\Tax\Helper\Data $vertexHelper
    ) {
        $this->vertexSMBHelper = $vertexHelper;
    }

    /**
     * requestType: TaxAreaRequest, InvoiceRequest, QuotationRequest
     *
     * @return multitype:unknown
     */
    public function exportAsArray()
    {
        $request = [
            'Login' => [
                'TrustedId' => $this->getTrustedId()
            ],
            $this->getRequestType() => [
                'documentDate' => $this->getDocumentDate(),
                'postingDate' => $this->getPostingDate(),
                'transactionType' => $this->getTransactionType(),
                'documentNumber' => $this->getDocumentNumber(),
                'LineItem' => []
            ]
        ];
        if ($this->getDocumentNumber()) {
            $request[$this->getRequestType()]['documentNumber'] = $this->getDocumentNumber();
        }

        $orderItems = $this->getOrderItems();
        $request[$this->getRequestType()]['LineItem'] = $this->addItems($orderItems);

        return $request;
    }

    /**
     * @param unknown $items
     * @return multitype:unknown
     */
    public function addItems($items)
    {
        $queryItems = [];
        $i = 1;
        /**
         * lineItemNumber
         */
        foreach ($items as $key => $item) {
            /**
             * $key - quote_item_id
             */
            $tmpItem = [
                'lineItemNumber' => $i,
                'lineItemId' => $key,
                'locationCode' => $this->getLocationCode(),
                'Seller' => [
                    'Company' => $this->getCompanyId(),
                    'PhysicalOrigin' => [
                        'StreetAddress1' => $this->getData('company_street_1'),
                        'StreetAddress2' => $this->getData('company_street_2'),
                        'City' => $this->getCompanyCity(),
                        'Country' => $this->getCompanyCountry(),
                        'MainDivision' => $this->getCompanyState(),
                        'PostalCode' => $this->getCompanyPostcode()
                    ]
                ],
                'Customer' => [
                    'CustomerCode' => [
                        'classCode' => $this->getCustomerClass(),
                        '_' => $this->getCustomerCode()
                    ],
                    'Destination' => [
                        'StreetAddress1' => $this->getCustomerStreet1(),
                        'StreetAddress2' => $this->getCustomerStreet2(),
                        'City' => $this->getCustomerCity(),
                        'MainDivision' => $this->getCustomerRegion(),
                        'PostalCode' => $this->getCustomerPostcode(),
                        'Country' => $this->getCustomerCountry()
                    ]
                ],
                'Product' => [
                    'productClass' => $item['product_class'],
                    '_' => $this->vertexSMBHelper->maxProductCodeOffset($item['product_code'])
                ],
                'UnitPrice' => $item['price']/*[
                    '_' => $item['price']
                ]*/,
                'Quantity' => $item['qty']/*[
                    '_' => $item['qty']
                ]*/,
                'ExtendedPrice' => $item['extended_price']/*[
                    '_' => $item['extended_price']
                ]*/
            ];

            if ($this->getCustomerCountry() == 'CAN') {
                $tmpItem['deliveryTerm'] = 'SUP';
            }

            if ($this->getTaxAreaId() && $this->getCustomerCountry() == 'USA') {
                $tmpItem['Customer']['Destination']['taxAreaId'] = $this->getTaxAreaId();
            }

            $queryItems[] = $tmpItem;
            $i ++;
        }

        return $queryItems;
    }
}
