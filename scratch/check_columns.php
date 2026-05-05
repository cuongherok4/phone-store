<?php
use Illuminate\Support\Facades\Schema;
echo json_encode(Schema::getColumnListing('order_status_histories'));
