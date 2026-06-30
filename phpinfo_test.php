<?php
echo "=== PHP Version ===\n";
echo phpversion() . "\n\n";
echo "=== PHP Settings ===\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n\n";
echo "=== PHP Handler ===\n";
echo php_sapi_name() . "\n";
