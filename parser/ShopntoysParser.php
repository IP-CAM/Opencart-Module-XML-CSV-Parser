<?php
require_once 'Parser.php';

class ShopntoysParser extends Parser
{
	private $categoryFile;
	private $productFile;
	private $contentFile;
	
	public function __construct($params)
	{
		parent::__construct($params);
	}
	
	protected function initializeParser()
	{
		$this->categoryFile = file_get_contents('https://shopntoys.ru/zpartnerapi/get/categories/?key=jhadiuhqwdeiu127271ASHDHQH'); //Путь до файла с категориями
		$this->productFile  = file_get_contents('https://shopntoys.ru/zpartnerapi/get/stock/?key=jhadiuhqwdeiu127271ASHDHQH'); //Путь до файла с продуктами
		$this->contentFile  = file_get_contents('https://shopntoys.ru/zpartnerapi/get/content/?key=jhadiuhqwdeiu127271ASHDHQH'); //Путь до файла с контентом
	}
	
	public function parserFunction()
	{
		$imageCount   = 0;
		$featureCount = 0;
		
		$products   = $this->getXml($this->productFile)->stock->product;
		$contents   = $this->getJsonXml($this->contentFile)->content->product;
		$categories = $this->getXml($this->categoryFile)->categories->category;
		
		foreach($contents as $content)
		{
			if(is_array($content->images->image) && $imageCount < count($content->images->image)) $imageCount = count($content->images->image);
			if(is_array($content->features->feature) && $featureCount < count($content->features->feature)) $featureCount = count($content->features->feature);
		}
		
		$this->setContent('Категория|Название|Sku|Цена|Количество|Описание|Бренд', -1);
		
		for($i = 0; $i < $imageCount; $i++) $this->setContent('image'.$i, -1);
		for($i = 0; $i < $featureCount; $i++)
		{
			$this->setContent('attribute'.$i, -1);
			$this->setContent('value'.$i, -1);
		}
		
		for($i = 0; $i < count($products); $i++)
		{
			foreach($categories as $category)
			{
				if((int) $products[$i]['category_id'] != (int) $category['id']) continue;
				$this->setContent((string) $category, $i);
				break;
			}
			$this->setContent((string) $products[$i]->name, $i);
			$this->setContent((string) $products[$i]->sku, $i);
			$this->setContent((int) $products[$i]->prices->price[1]->value, $i);
			$this->setContent((int) $products[$i]->count, $i);
			$this->setContent($this->deleteSpecialChar($contents[$i]->description, true), $i);
			$this->setContent($contents[$i]->brand, $i);
			
			if(!is_array($contents[$i]->images->image))
			{
				$this->setContent($contents[$i]->images->image, $i);
				for($j = 0; $j < $imageCount - 1; $j++) $this->setContent('-', $i);
			}
			else
			{
				for($j = 0; $j < $imageCount; $j++) $this->setContent($contents[$i]->images->image[$j], $i);
			}
			
			if(!is_array($contents[$i]->features->feature))
			{
				$this->setContent($contents[$i]->features->feature->name, $i);
				$this->setContent($contents[$i]->features->feature, $i);
			}
			else
			{
				for($j = 0; $j < count($contents[$i]->features->feature); $j++)
				{
					$this->setContent($contents[$i]->features->feature[$j]->name, $i);
					if(!is_array($contents[$i]->features->feature[$j]->value)) $this->setContent($contents[$i]->features->feature[$j]->value, $i);
					else
					{
						$arrValue = null;
						for($k = 0; $k < count($contents[$i]->features->feature[$j]->value); $k++) $arrValue = $arrValue.' '.$contents[$i]->features->feature[$j]->value[$k];
						$this->setContent($arrValue, $i);
						unset($arrValue);
					}
				}
			}
		}
		if($this->logOut) $this->createLog('shopntoys.log', 'Товары');
	}
}

$file = new ShopntoysParser('shopntoys');
$file->createFile();
?>