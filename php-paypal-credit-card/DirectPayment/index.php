<html>
    <head>
        <title>paypal payments via credit card</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <meta name="robots" content="noindex, nofollow">
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-43981329-1']);
            _gaq.push(['_trackPageview']);
            (function() {
                var ga = document.createElement('script');
                ga.type = 'text/javascript';
                ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(ga, s);
            })();
        </script>
    </head>
    <body>

        <div id="main">
            <center><h1>PayPal Payments via Credit Card</h1></center>
            <div id="main_wrapper">
                <div id="container">
                    <h2>BEST SELLER</h2>
                    <hr/>
                    <form action="getcreditcard.php" method="POST">
                        <div id="product">
                            <img src="images/bag2.jpg"/>
                            <p>Tas Ransel Orange bag</p>
                            <h3>$21.10</h3>
                            <input type="hidden" value="<?php echo base64_encode(1); ?>" name="id">
                            <!-- <a href="#" id="buynow"> Buy Now </a> -->
                            <center><input type="submit" id="buynow" value="Buy Now"></center>
                        </div>
                    </form>
                    <form action="getcreditcard.php" method="POST">
                        <div id="product">
                            <img src="images/bag.jpg"/>
                            <p>Tas Ransel Jeans bag</p>
                            <h3>$15.20</h3>
                            <input type="hidden" value="<?php echo base64_encode(2); ?>" name="id">
                            <!--  <a href="#" id="buynow"> Buy Now </a> -->
                            <center><input type="submit" id="buynow" value="Buy Now"></center>
                        </div>
                    </form>
                    <form action="getcreditcard.php" method="POST">
                        <div id="product">
                            <img src="images/bag4.jpg"/>
                            <p>Tas Ransel grey bag</p>
                            <h3>$11.10</h3>
                            <input type="hidden" value="<?php echo base64_encode(3); ?>" name="id">
                            <!--  <a href="#" id="buynow"> Buy Now </a> -->
                            <center><input type="submit" id="buynow" value="Buy Now"></center>
                        </div>
                    </form>
                </div>
            </div>
            <img id="paypal_logo" style="float:right;   margin: -30px -42px 0 0;" src="images/secure-paypal-logo.jpg">
        </div>
    </body>
</html>
