<?php
require_once 'Parser.php';

class SntParser extends Parser
{
	private $pages = 160;
	
	private $remainsFile;
	private $productsFile;
	
	public function __construct($params) 
	{
        parent::__construct($params);
    }
	
	protected function initializeParser()
	{
		$this->productsFile = file_get_contents('http://snt.su/api/products/info?key=ab7a151139a812e54a91eafa9a3be78f');
		
		$mh = curl_multi_init();
		
		for($i = 1; $i < $this->pages; $i++) 
		{
			$conn[$i] = curl_init('http://snt.su/api/products/pages/?page='.$i.'&key=ab7a151139a812e54a91eafa9a3be78f');
			curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($conn[$i], CURLOPT_CONNECTTIMEOUT, 10);
			curl_multi_add_handle($mh, $conn[$i]);
		}

		do 
		{ 
			curl_multi_exec($mh, $active); 
		} 
		while($active);
		
		for($i = 1; $i < $this->pages; $i++) 
		{
			$this->remainsFile[$i] = json_decode(curl_multi_getcontent($conn[$i])); 
			curl_multi_remove_handle($mh, $conn[$i]);
			curl_close($conn[$i]);
		}
		curl_multi_close($mh);
	}
	
	public function parserFunction()
	{
		$imageCount = 0;
		$featureCount = 0;

		$products = $this->getJsonXml($this->productsFile)->content->product;
		$categories = $this->getXml($this->productsFile)->categories->category;
		
		foreach($products as $product)
		{
			if(is_array($product->images->image) && $imageCount < count($product->images->image)) $imageCount = count($product->images->image);
			if(is_array($product->features->feature) && $featureCount < count($product->features->feature)) $featureCount = count($product->features->feature);
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
			$endTeg = NULL;
			$tagArr = explode(',', $products[$i]->category);
			foreach($categories as $category)
			{
				foreach($tagArr as $newTag)
				{
					if($newTag != $category['id']) continue;
					$endTeg = $endTeg.' '.$category;
				}
			}
			$this->setContent($endTeg, $i);
			
			$this->setContent($products[$i]->name, $i);
			$this->setContent($products[$i]->sku, $i);
			
			foreach($this->remainsFile as $remain)
            {
                if(empty($remain->{$products[$i]->sku})) continue;
                $this->setContent($remain->{$products[$i]->sku}->price_wcwpd, $i);
                $this->setContent($remain->{$products[$i]->sku}->in_stock, $i);
                break;
            }

			$this->setContent($this->deleteSpecialChar($products[$i]->description, true), $i);
			$this->setContent($products[$i]->brand, $i);
			
			if(!is_array($products[$i]->images->image))
			{
				$this->setContent($products[$i]->images->image, $i);
				for($j = 0; $j < $imageCount-1; $j++) $this->setContent('-', $i);
			}
			else
			{
				for($j = 0; $j < $imageCount; $j++) $this->setContent($products[$i]->images->image[$j], $i);
			}
				
			if(!is_array($products[$i]->features->feature))
			{
				$this->setContent($products[$i]->features->feature->name, $i);
                $this->setContent($products[$i]->features->feature, $i);
			}
			else
			{
				for($j = 0; $j < count($products[$i]->features->feature); $j++)
                {
					$this->setContent($products[$i]->features->feature[$j]->name, $i);
					if(!is_array($products[$i]->features->feature[$j]->value)) $this->setContent($products[$i]->features->feature[$j]->value, $i);
					else
					{
						$arrValue = NULL;
						for($k = 0; $k < count($products[$i]->features->feature[$j]->value); $k++) $arrValue = $arrValue.' '.$products[$i]->features->feature[$j]->value[$k];
						$this->setContent($arrValue, $i);
						unset($arrValue);
					}
				}
			}
		}
		if($this->logOut) $this->createLog('snt.log', 'Товары');
	}
}

$file = new SntParser('snt');
$file->createFile();