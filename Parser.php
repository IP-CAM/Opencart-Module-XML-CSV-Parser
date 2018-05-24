<?php
abstract class Parser
{
	protected $logOut;
	protected $delLim;
	protected $outFile;
	
	private $arrayList;
	
	abstract protected function initializeParser();
	abstract public function parserFunction();
	
	public function __construct(string $inputFile, string $delLimiter = '|')
    {
        $this->initializeParser();
        $this->delLim = $delLimiter;
		$this->outFile = date("H.i.s").'_'.date("d-m-Y").'_'.$inputFile;
    }

    public function deleteSpecialChar($text, bool $type = false)
    {
        if(is_object($text) || !strlen($text)) return '-';
		if($type) $text = str_replace($this->delLim, ';', $text);
		else
		{
			$text = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $text);
			$text = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $text);
		}
        return $text;
    }
	
	public function getJsonXml($xml)
	{
		return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)));
	}
	
	public function getXml($xml)
	{
		return simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	}
	
	public function getContent()
    {
        return $this->arrayList;
    }
	
	public function setContent($string, int $index)
	{
		$index = $index+1;
		if(is_object($string) || !strlen($string)) $this->arrayList[$index] = $this->arrayList[$index].'-'.$this->delLim;
		else $this->arrayList[$index] = $this->arrayList[$index].$string.$this->delLim;
	}
	
	public function saveCsv(array $content) 
	{
        $handle = fopen($this->outFile, 'w'); 
        foreach($content as $value) 
		{ 
			$value = $this->deleteSpecialChar($value);
            fputcsv($handle, explode($this->delLim, $value), $this->delLim); 
        }
        fclose($handle);
		echo 'Файл '.$this->outFile.' успешно создан.';
    }
	
	public function saveXML(array $content) 
	{
		$doc = new DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;

		$root = $doc->createElement('rows');
		$root = $doc->appendChild($root);
		
		$headers = explode($this->delLim, $content[0]);
		unset($content[0]);
		
		foreach($content as $row) 
		{
			$row = explode($this->delLim, $this->deleteSpecialChar($row));
			$container = $doc->createElement('row');
			foreach($headers as $i => $header)
			{
				if(!strlen($header)) continue;
				$child = $doc->createElement($header);
				$child = $container->appendChild($child);
				$value = $doc->createTextNode($row[$i]);
				$value = $child->appendChild($value);
			}
			$root->appendChild($container);
		}
		
		$handle = fopen($this->outFile, 'w');
		fwrite($handle, $doc->saveXML());
		fclose($handle);
		
		echo 'Файл '.$this->outFile.' успешно создан.';
    }
	
	public function createLog(string $file, string $type) 
	{
		$put = array
		(
		  'file' => $this->outFile,
		  'time' => date("H:i"),
		  'date' => date("d.m.Y"),
		  'type' => $type
		);
		file_put_contents('logs/'.$file, json_encode($put)."\r\n", FILE_APPEND | LOCK_EX);
    }
	
	public function createFile(bool $type = true, bool $logging = true)
	{
		if(!$type) $this->outFile = 'csv/'.$this->outFile.'.csv';
		else $this->outFile = 'xml/'.$this->outFile.'.xml';
		
		$this->logOut = $logging;
		
		$this->parserFunction();
		
		if(!$type) $this->saveCsv($this->getContent());
		else $this->saveXml($this->getContent());
	}
}	