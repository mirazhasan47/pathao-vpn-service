<?php
include 'class.smpp.php';
$src  = "PayStation";
$dst  = "8801911242148";
$message = "SMPP SMS TEST BL, 201:Successfully TopUp Tk 10 to number, Transaction ID BD1111111 Comm:0.20Curent Balance Tk2000.90";
$s = new smpp();
$s->debug=1;
$s->open("10.10.78.43", 6017, "paystation", "p@ySton1");
$s->send_long($src, $dst, $message);
$s->close();
?>