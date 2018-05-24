<?php
require_once 'Parser.php';

class ShopntoysPriceParser extends Parser
{
	private $productFile;
	
	public function __construct($params) 
	{
        parent::__construct($params);
    }
	
	protected function initializeParser()
	{
		$this->productFile = file_get_contents('https://shopntoys.ru/zpartnerapi/get/stock/?key=jhadiuhqwdeiu127271ASHDHQH'); //Путь до файла с продуктами
	}
	
	public function parserFunction()
	{
		$products = $this->getJsonXml($this->productFile)->stock->product;

		$this->setContent('Категория|Название|Sku|Цена|Количество', -1);
		
		for($i = 0; $i < count($products); $i++)
		{
            $this->setContent('-', $i);
            $this->setContent($products[$i]->name, $i);
            $this->setContent($products[$i]->sku, $i);
            $this->setContent($products[$i]->prices->price[1]->value, $i);
            $this->setContent($products[$i]->count, $i);
		}
	}
}

$file = new ShopntoysPriceParser('shopntoys_price');
$file->createFile();