<?php
$hash = '$2y$10$F0kCzZynL2z7v9AlBkMCwuzWLT0mdUimfWhCrivMByrSOKrvkRO82';
$try = 'admin123';

if (password_verify($try, $hash)) {
    echo "Match — password is admin123\n";
} else {
    echo "No match\n";
}
?>
