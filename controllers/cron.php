<?php

//update github 
try{
	Log::getInstance()->log("[cron] Updating github information", true);
	Github::cron();
} catch ( Exception $ex ) {
	Log::getInstance()->log("[cron] github exception: {$ex->getMessage()}. Trace: {$ex->getTraceAsString()}", true);
}


//update tickspot
try{
	Log::getInstance()->log("[cron] Updating tickspot information", true);
	TickSpot::cron();
} catch ( Exception $ex ) {
	Log::getInstance()->log("[cron] tickspot exception: {$ex->getMessage()}. Trace: {$ex->getTraceAsString()}", true);
}
