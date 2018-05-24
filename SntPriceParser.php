<?php
require_once 'Parser.php';

class SntPriceParser extends Parser
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
		$products = $this->getJsonXml($this->productsFile)->content->product;

		$this->setContent('Категория|Название|Sku|Цена|Количество', -1);
		
		for($i = 0; $i < count($products); $i++)
		{
            $this->setContent('-', $i);
            $this->setContent($products[$i]->name, $i);
			$this->setContent($products[$i]->sku, $i);
			
			foreach($this->remainsFile as $remain)
            {
                if(empty($remain->{$products[$i]->sku})) continue;
                $this->setContent($remain->{$products[$i]->sku}->price_wcwpd, $i);
                $this->setContent($remain->{$products[$i]->sku}->in_stock, $i);
                break;
            }
		}
	}
}

$file = new SntPriceParser('snt_price');
$file->createFile();