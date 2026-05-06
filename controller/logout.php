<?php
session_start();

session_unset();   // يمسح بيانات المستخدم
session_destroy(); // يقتل السيشن

header("Location: ../view/front/login.php");
exit();