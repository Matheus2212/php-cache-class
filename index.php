<?php
include("CacheClass.php");

Cache::init();

?>


<!DOCTYPE html>
<html lang="en">

<head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
</head>

<body>
        Bom, aqui daí seria o cache todo né...
        <form action="" method="post">
                <input type="text" name="check">
                <input type="submit" value="enviar" />
        </form>
</body>

</html>

<?php

Cache::end();

?>
