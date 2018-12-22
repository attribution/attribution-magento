<h1>Attribution for Magento 1</h1>
<p>This is Magento plugin for <a href="https://attributionapp.com" rel="nofollow">Attribution</a>.</p>

<h2>Installation</h2>
<ol>
 <li>Download the Attribution for Magento 1 plugin - Available as a .zip or tar.gz file from the Attribution GitHub directory.
 <li>Unzip content of archive. 
 <li>Copy extracted data to your Magento root folder. If Magento is installed on remote server - use FTP file manager (like FileZilla) to connect to your server and copy extracted files to Magento root directory.
 <li>Log in to your Magento admin panel.
 <li>Flush cache.
 <li>Navigate to <code>System->Configuration->Attribution->
Attribution App</code>.
 <li>In General tab enable plugin by setting "Enabled" option to "Yes".
 <li>Enter your Attribution Account ID and save settings.
 <li>Flush cache again.
 </ol>
 Attribution plugin will automatically track events and send data to Attribution account.
 
<h3>Plugin will track next event:</h3> 
<ul>
   <li> "Sign Up" - track customer registration data (Name, Email, Exact time of registration)
   <li> "Login" - track when customer log in
   <li> "Purchase Complete" - track new order data (Order revenue, if customer is registered - customer total orders amount and lifetime sales will be tracked too)
   <li> "Order refund" - track credit memo data
  </ul>
   
   
