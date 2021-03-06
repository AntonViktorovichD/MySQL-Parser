<?php

function error_logger($e)
{
   $log_dir = 'log/';
   if (!is_dir($log_dir)) {
      mkdir($log_dir);
   }
   date_default_timezone_set("Europe/Moscow");
   echo $error_message = date("d.m.y H-i-s ") . $e->getMessage() . PHP_EOL;
   $fp = fopen($log_dir . "error_log.txt", "a");
   fwrite($fp, $error_message);
   fclose($fp);
   exit(mysqli_connect_errno());
}

function connect_db()
{
   try {
      $db = mysqli_connect(HOST, USER, PASSWORD, DB);
      if (mysqli_connect_error($db)) {
         throw new Exception('Невозможно подключиться к базе данных');
      }
   } catch (Exception $e) {
      error_logger($e);
   }
   return $db;
}

function get_tables($db)
{
   try {
      $sql = "SHOW TABLES";
      $result = mysqli_query($db, $sql);
      if (!$result) {
         throw new Exception('Невозможно получить таблицы базы данных');
      }
   } catch (Exception $e) {
      error_logger($e);
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
         $fp = fopen(DIR_SQL . $item . "_dump.sql", "w");
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

function db_archive()
{
   $path_dir = 'sql/';
   $name_archive = date("d.m.y_H-i-s ") . 'sql_dump.zip';
   $zip = new ZipArchive;
   if ($zip->open($name_archive, ZipArchive::CREATE) === TRUE) {
      $dir = opendir($path_dir);
      while ($file = readdir($dir)) {
         if (is_file($path_dir . $file)) {
            $zip->addFile($path_dir . $file, $file);
            echo("Заархивирован: " . $path_dir . $file), '</br>';
         }
      }
      $zip->close();
      echo 'Архив успешно создан';
      array_map('unlink', glob("$path_dir/*.*"));
      rmdir($path_dir);
   }
   try {
      if (!file_exists($name_archive)) {
         throw new Exception('Произошла ошибка при создании архива');
      }
   } catch (Exception $e) {
      error_logger($e);
   }
}

function log_archive()
{
   $path_dir = 'log/';
   if (file_exists($path_dir . 'error_log.txt')) {
      if (filesize($path_dir . 'error_log.txt') >= 5242880) {
         date_default_timezone_set("Europe/Moscow");
         $name_archive = date("d.m.y_H-i-s ") . 'error_log_archive.zip';
         $zip = new ZipArchive;
         if ($zip->open($name_archive, ZipArchive::CREATE) === TRUE) {
            $dir = opendir($path_dir);
            while ($file = readdir($dir)) {
               if (is_file($path_dir . $file)) {
                  $zip->addFile($path_dir . $file, $file);
                  echo('</br>' . "Заархивирован log: " . $path_dir . $file), '</br>';
               }
            }
            $zip->close();
         }
         array_map('unlink', glob("$path_dir/*.*"));
         rmdir($path_dir);
      }
      try {
         if (!file_exists($name_archive)) {
            throw new Exception('Произошла ошибка при архивации "error_log.txt"');
         }
      } catch (Exception $e) {
         error_logger($e);
      }
   }
}

?>