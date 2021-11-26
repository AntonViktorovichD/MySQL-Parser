<?php

function connect_db()
{
   $db = mysqli_connect(HOST, USER, PASSWORD, DB);

   if (mysqli_connect_error($db)) {
      exit("Нет соединения с БД");
   }

   return $db;
}

function get_tables($db)
{

   $sql = "SHOW TABLES";
   $result = mysqli_query($db, $sql);

   if (!$result) {
      exit(mysqli_error($db));
   }
   $tables = array();
   for ($i = 0; $i < mysqli_num_rows($result); $i++) {
      $row = mysqli_fetch_row($result);
      $tables[] = $row[0];
   }
   return $tables;
}

function get_dump($db, $tables)
{
   $sql_dir = 'sql';
   if (!is_dir($sql_dir)) {
      mkdir($sql_dir);
   }
   if (is_array($tables)) {
      foreach ($tables as $item) {
         date_default_timezone_set("Europe/Moscow");
         $fp = fopen(DIR_SQL . date("m.d.y_H-i ") . $item . "_dump.sql", "w");

         $text = "";
         $sql = "SHOW CREATE TABLE " . $item;
         $result = mysqli_query($db, $sql);
         if (!$result) {
            exit(mysqli_error($db));
         }
         $row = mysqli_fetch_row($result);

         $text .= "\n" . $row[1] . ";";

         $text .= "\nINSERT INTO `" . $item . "` VALUES";
         fwrite($fp, $text);

         $sql2 = "SELECT * FROM " . $item;
         $result2 = mysqli_query($db, $sql2);
         if (!$result2) {
            exit(mysqli_error($db));
         }
         $text = "";

         for ($i = 0; $i < mysqli_num_rows($result2); $i++) {
            $row = mysqli_fetch_row($result2);

            if ($i == 0) $text .= "(";
            else  $text .= ",(";

            foreach ($row as $v) {
               $text .= "\"" . mysqli_real_escape_string($db, $v) . "\",";
            }
            $text = rtrim($text, ",");
            $text .= ")";

            if ($i > 'FOR_WRITE') {
               fwrite($fp, $text);
               $text = "";
            }
         }
         $text .= ";\n";
         fwrite($fp, $text);
      }
      fclose($fp);
   }
}

function make_archive()
{
   $pathdir = 'sql/';
   $name_arhive = date("m.d.y_H-i ") . 'sql_dump.zip';
   $zip = new ZipArchive;
   if ($zip->open($name_arhive, ZipArchive::CREATE) === TRUE) {
      $dir = opendir($pathdir); // открываем папку с файлами
      while ($file = readdir($dir)) {
         if (is_file($pathdir . $file)) {
            $zip->addFile($pathdir . $file, $file);
            echo("Заархивирован: " . $pathdir . $file), '<br/>';
         }
      }
      $zip->close();
      echo 'Архив успешно создан';
      array_map('unlink', glob("$pathdir/*.*"));
      rmdir($pathdir);
   } else {
      die ('Произошла ошибка при создании архива');
   }
}

//$files = array_diff(scandir('sql'), ['..', '.']);
//
//$zip = new ZipArchive;
//if ($zip->open('test.zip') === TRUE) {
//   foreach ($files as $file) {
//      $zip->addFile('sql . ' . $file . '.sql');
//   }
//   $zip->close();
//   echo 'готово';
//} else {
//   echo 'ошибка';
//}

//function connect_db()
//{
//   $db = mysqli_connect(HOST, USER, PASSWORD, DB);
//
//   if (mysqli_connect_error($db)) {
//      exit("Нет соединения с БД");
//   }
//
//   return $db;
//}
//
//function get_tables($db)
//{
//
//   $sql = "SHOW TABLES";
//   $result = mysqli_query($db, $sql);
//
//   if (!$result) {
//      exit(mysqli_error($db));
//   }
//   $tables = array();
//   for ($i = 0; $i < mysqli_num_rows($result); $i++) {
//      $row = mysqli_fetch_row($result);
//      $tables[] = $row[0];
//   }
//   return $tables;
//}
//
//function get_dump($db, $tables)
//{
//   $sql_dir = 'sql';
//   if (!is_dir($sql_dir)) {
//      mkdir($sql_dir);
//   }
//   if (is_array($tables)) {
//      date_default_timezone_set("Europe/Moscow");
//      $fp = fopen(DIR_SQL . date("m.d.y_H-i-s") . `" . DB . "` . "_dump.sql", "w");
//
//      $text = "-- SQL Dump
//--
//-- База дынных: `" . date("m.d.y_H-i-s") . `" . DB . "` . "`
//--";
//      fwrite($fp, $text);
//
//      foreach ($tables as $item) {
//
//         $text = "
//--
//-- Структура таблицы - " . $item . "
//--
//";
//         fwrite($fp, $text);
//
//         $text = "";
//         $text .= "DROP TABLE IF EXISTS `" . $item . "`;";
//         $sql = "SHOW CREATE TABLE " . $item;
//         $result = mysqli_query($db, $sql);
//         if (!$result) {
//            exit(mysqli_error($db));
//         }
//         $row = mysqli_fetch_row($result);
//
//         $text .= "\n" . $row[1] . ";";
//         fwrite($fp, $text);
//
//         $text = "";
//         $text .=
//            "
//--
//-- Dump BD - tables :" . $item . "
//--
//			";
//         $text .= "\nINSERT INTO `" . $item . "` VALUES";
//         fwrite($fp, $text);
//
//         $sql2 = "SELECT * FROM " . $item;
//         $result2 = mysqli_query($db, $sql2);
//         if (!$result2) {
//            exit(mysqli_error($db));
//         }
//         $text = "";
//
//         for ($i = 0; $i < mysqli_num_rows($result2); $i++) {
//            $row = mysqli_fetch_row($result2);
//
//            if ($i == 0) $text .= "(";
//            else  $text .= ",(";
//
//            foreach ($row as $v) {
//               $text .= "\"" . mysqli_real_escape_string($db, $v) . "\",";
//            }
//            $text = rtrim($text, ",");
//            $text .= ")";
//
//            if ($i > FOR_WRITE) {
//               fwrite($fp, $text);
//               $text = "";
//            }
//         }
//         $text .= ";\n";
//         fwrite($fp, $text);
//      }
//   }
//   fclose($fp);
//}
//
?>