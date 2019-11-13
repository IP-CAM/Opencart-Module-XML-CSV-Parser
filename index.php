<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Q-Cars! Create tables</title>

        <meta name="description" content="Q-Cars! Create tables">
        <meta name="author" content="Q-Cars!">

        <link href="css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <br/><br/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <br/>
                    <h3 class="text-center">SNT:</h3><br/>
                    <form class="text-center" onsubmit="disableBtn()">
                        <button id="btn0" formaction="parser/SntParser.php" type="submit" class="btn btn-success btn-lg" style="margin: 0 40px;">Товары</button>
                        <button id="btn1" formaction="parser/SntPriceParser.php" type="submit" class="btn btn-success btn-lg" style="margin: 0 40px;">Цены</button>
                    </form>
                    <br/>
                    <table class="table table-hover table-sm">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Тип</th>
                            <th>Размер</th>
                            <th>Файл</th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						if(file_exists('parser/logs/snt.log'))
						{
							$count = 0;
							$snt   = json_decode(file_get_contents('parser/logs/snt.log'), true);
							for($i = count($snt); $i > count($snt) - 10; $i--)
							{
								if(empty($snt[$i])) continue;
								$file = 'parser/'.$snt[$i]['file'];
								$count++;
								
								echo '<tr class = "table-success">
								<td>'.$count.'</td>
								<td>'.$snt[$i]['date'].'</td>
								<td>'.$snt[$i]['time'].'</td>
								<td>'.$snt[$i]['type'].'</td>
								<td>'.round((filesize($file) / 1024 / 1024), 2).' МБ</td>
								<td><a href=""'.$file.' download>Скачать</a> <img src="images/cloud-download.svg"></td>
								</tr>';
							}
						}
						?>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <br/>
                    <h3 class="text-center">Shopntoys:</h3><br/>
                    <form class="text-center" onsubmit="disableBtn()">
                        <button id="btn2" formaction="parser/ShopntoysParser.php" type="submit" class="btn btn-success btn-lg" style="margin: 0 40px;">Товары</button>
                        <button id="btn3" formaction="parser/ShopntoysPriceParser.php" type="submit" class="btn btn-success btn-lg" style="margin: 0 40px;">Цены</button>
                    </form>
                    <br/>
                    <table class="table table-hover table-sm">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Тип</th>
                            <th>Размер</th>
                            <th>Файл</th>
                        </tr>
                        </thead>
                        <tbody>
						<?php
						if(file_exists('parser/logs/shopntoys.log'))
						{
							$count     = 0;
							$shopntoys = json_decode(file_get_contents('parser/logs/shopntoys.log'), true);
							for($i = count($shopntoys); $i > count($shopntoys) - 10; $i--)
							{
								if(empty($shopntoys[$i])) continue;
								$file = 'parser/'.$shopntoys[$i]['file'];
								$count++;
								
								echo '<tr class = "table-success" onclick = "window.location.href=$file">
								<td>'.$count.'</td>
								<td>'.$shopntoys[$i]['date'].'</td>
								<td>'.$shopntoys[$i]['time'].'</td>
								<td>'.$shopntoys[$i]['type'].'</td>
								<td>'.round((filesize($file) / 1024 / 1024), 2).' МБ</td>
								<td><a href=""'.$file.' download>Скачать</a> <img src="images/cloud-download.svg"></td>
								</tr>';
							}
						}
						?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/scripts.js"></script>
        <script>
            function disableBtn()
            {
                document.getElementById('btn0').disabled = true;
                document.getElementById('btn1').disabled = true;
                document.getElementById('btn2').disabled = true;
                document.getElementById('btn3').disabled = true;
            }
        </script>
    </body>
</html>