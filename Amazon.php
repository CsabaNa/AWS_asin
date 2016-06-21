<?php

    namespace Amazon;

    class AmazonException extends \Exception
    {
        /**
         * @todo Error/Exception msg
         * @var string
         */
        protected $strMsg;

        function __construct($argStrNotice,$argStrSevereity=0,  $argPrevious = NULL)
        {
            parent::__construct($argStrNotice, $argStrSevereity, $argPrevious);
            $this->strMsg       = $argStrNotice;
        }

        public function __toString ()
        {
            return $this->strMsg;
        }

        public function GetMsg ()
        {
            return $this->strMsg;
        }
    } // class AmazonException extends \Exception end

    interface iAmazon
    {

        /**
         * @todo This function seting up the country details: code, TLD, URL
         * @param string $argCC the country code
         *
         * @return boolean
         */
        public function setCountry($argCC);

    } // interface iAmazon end

    abstract class setAmazon implements iAmazon
    {

        /**
         * @todo the country code store (hide and protect) setable
         * @access private
         * @var string
         */
        private $strCountryCode = 'GB';

        /**
         * @todo the country TLD store (hide and protect)
         * @access private
         * @var string
         */
        private $strTLD = 'co.uk';

        /**
         * @todo Amazon URL pattern: http://www.amazon.{COUNTRY_TLD}/dp/{ASIN} (hide and protect)
         *      {ASIN} will be joined by getItem(),
         *      at the moment no setable from outside
         * @access private
         * @var string
         */
        private $strAmazonURL = 'http://www.amazon.%1$s/xml/dp/';

        /**
         * some things which is can setup automaticaly (though it shoud be better from any DBase)
         * @todo TLD match
         *      at the moment no setable from outside
         * @access private
         * @var array
         */
        private $arrTLDMatch = array(
            'GB' => 'co.uk',
            'US' => 'com',
        );

        /**
         * @todo Query params: Title, price, img tag name in the HTML. setable
         * @access private
         * @var array
         */
        private $arrQParamsFromHTML = array(
            'title' => array(
                    'root'        => '//*/span[@id="productTitle"]',
                    'attributes'  => false),
            'price' => array(
                    'root'        => '//*/div[@id="price"]//span[@id="priceblock_ourprice"]',
                    'attributes'  => false),
            'imageUrl' => array(
                    'root'        => '//*/img[@id="landingImage"]',
                    'attributes'  => 'src'),
        );

        /**
         * @todo some protected and private variable is availebled (just read, if it is necessary).
         * If it is not exists it is droped standard error
         * @param mixed $argName
         * @return mixed
         */
        public function __get($argName)
        {
            if( isset($this->{$argName}) ) { return $this->{$argName}; }
        } // public function __get($argName){ end

        /**
         * @todo some varriable is setable and it can verified (more check/validity)
         *      setable var(s):
         *          strCountryCode or construct parameter name: iso3166_1Alpha2CountryCode
         *      otherwise PHP standard error
         * @param string $argVarName class varriable name
         * @param mixed $argVarValue varriable value
         */
        public function __set($argVarName,$argVarValue)
        {
            switch("$argVarName")
            {
                // constructor param name
                case 'iso3166_1Alpha2CountryCode':
                // field name
                case 'strCountryCode':
                    if( $this->setCountry($argVarValue) ) { return true; }
                    break;
                    // case 'strCountryCode': end
                // Query parameter setting
                case 'arrQParamsFromHTML':
                    if( is_array($argVarValue) )
                    {
                        $this->arrQParamsFromHTML = $argVarValue;
                    }
                    break;
                    // case 'arrQParamsFromHTML': end
            } // switch("$argVarName") { end
        } // public function __set() { end

        /**
         * @todo This function seting up the country details: code, TLD, URL
         * @param string $argCC the country code
         *
         * @return boolean
         */
        public function setCountry($argCC)
        {
            if( isset($this->arrTLDMatch[$argCC]) )
            {
                $this->strCountryCode = $argCC;
                $this->strTLD         = $this->arrTLDMatch[$argCC];
                $this->strURL         = sprintf($this->strAmazonURL,$this->strTLD);
                return true;
            } // if( isset($this->arrTLDMatch[$argVarValue]) ) { end

            return false;
        } // public function setCountry($argCC) { end

        /**
         * @todo cat some annoying things from the html
         *      tags: html, head, body, style, link, meta, javascript, comment,
         *          map, header, hr, form, input, a, p, 2 or more whitespace
         * @param string $argStr HTML string
         * @return string
         */
        public function clearHtml($argStr)
        {
            $ret = preg_replace(
                    array(
                        "/(<!.*>)/Uis",
                        "/(<html.*>)/Uis",
                        "/<head>(.*)<\/head>/Uis",
                        "/(<script.*>)(.*)<\/script>/Uis",
                        "/(<style.*>)(.*)<\/style>/Uis",
                        "/(<link.*>)/Uis",
                        "/(<meta.*>)/Uis",
                        "/(<body.*>)/Uis",
                        "/(<noscript.*>)(.*)<\/noscript>/Uis",
                        "/(<map.*>)(.*)<\/map>/Uis",
                        "/(<header.*>)(.*)<\/header>/Uis",
                        "/(<hr.*>)/Uis",
                        "/(<hr.*>)(.*)<\/hr>/Uis",
                        "/(<form.*>)(.*)<\/form>/Uis",
                        "/(<input.*>)/Uis",
                        "/(<a.*>)/Uis",
                        "/(<\/a>)/Uis",
                        "/(<p.*>)(.*)<\/p>/Uis",
                        "/({.*})/Uis",
                        "/(&nbsp;){2,}/Uis",
                        "/([ ]){2,}/Uis",
                        "/([\t])/Uis",
                        "/(?:\r){2,}/Uis",
                        "/(?:\n){2,}/Uis",
                        "/^(?:\r)$/Uis",
                        "/^(?:\n)$/Uis",
                        "/^(?:\r\n)$/Uis",
                        "/(<\/body>)/Uis",
                        "/(<\/html>)/Uis",
                    ),'',$argStr);
            return $ret;
        } // public static function clearHtml($argStr) end

    } // abstract class setAmazon { end

    class Amazon extends setAmazon
    {

        /**
         * @todo this is the exactly URL to amazon
         * @var string
         */
        protected $strURL;

        public function __construct($iso3166_1Alpha2CountryCode)
        {
            $this->setCountry(strtoupper($iso3166_1Alpha2CountryCode));
        } // public function __construct($iso3166_1Alpha2CountryCode) end

        /**
         * @todo Provided ->getItem($asin) method should return an array containing title,
         *      price and imageUrl for the requested product or throw an exception if data
         *      can't be returned e.g. product doesn't exists, is out of stock etc.
         * @param string $asin
         *
         * @return array/exeption
         */
        public function getItem($asin)
        {
            $ret = array();

            // XML
            $objXPath = $this->_getHtmlStringFromUrl($this->strURL.$asin);
            if( !($objXPath instanceof \DOMXPath) )
            {
                throw new AmazonException('Missing Amazon xml!');
            }

            // availability
            $tags = $objXPath->query('//*/div[@id="availability"]//span[@class="a-size-medium a-color-success"]');
            if( $tags instanceof \DOMNodeList && $tags->length>0 )
            {
                foreach ($tags as $tag)
                {
                    if( trim($tag->nodeValue)!='In stock.' )
                    {
                        throw new AmazonException('Out of stock!');
                    }
                } // foreach ($tags as $tag) { end
            } // if( $tags ) { end
            else
            {
                throw new AmazonException('No Item!');
            }

            // title; price; img, etc.
            foreach( $this->arrQParamsFromHTML as $key => $arrParamAtt )
            {
                // parameter root load
                $tags = $objXPath->query($arrParamAtt['root']);

                if( $tags instanceof \DOMNodeList && $tags->length>0 )
                {
                    foreach ($tags as $tag)
                    {
                        if( !$arrParamAtt['attributes'] )
                        {
                            $ret[$key][] = $tag->nodeValue;
                        }
                        else
                        {
                            $ret[$key][] = $tag->getAttribute ( $arrParamAtt['attributes'] );
                        }
                    } // foreach ($tags as $tag) { end
                } // if( $tags ) { end
                else
                {
                    throw new AmazonException('No Item Parameter: '.$key);
                }
            } // foreach( $this->arrQParamsFromHTML as $key => $arrHTMLIdValue ) { end
            return $ret;
        } // public function getItem($asin) end

        protected function _getHtmlStringFromUrl($url)
        {
            $rC = curl_init();
            curl_setopt($rC, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
            curl_setopt($rC, CURLOPT_URL, $url);
            curl_setopt($rC, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($rC);

            if( $res )
            {
                curl_close($rC);
                $ret = $this->clearHtml($res);
                return $this->_getDOMXpathFromHtmlString($ret);
            }
            else
            {
                throw new AmazonException('Amazon connection faild!');
            }
        } // protected function _getHtmlStringFromUrl($url) end

        protected function _getDOMXpathFromHtmlString($htmlString)
        {
            $domDoc = new \DOMDocument();

            @$domDoc->loadHtml('<?xml encoding="UTF-8">' .$htmlString);

            return new \DOMXPath( $domDoc );
        } // protected function _getDOMXpathFromHtmlString($htmlString) end

    } // class Amazon extends setAmazon end


    if( !isset($_REQUEST['country'])) {
        $_REQUEST['country'] = 'GB';
    }
    if( !isset($_REQUEST['asin'])) {
        $_REQUEST['asin'] = 'B00KDRUCJY';
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Amazon XML test</title>
        <meta name="google-site-verification" content="" />
	<meta charset="utf-8" />
        <meta name="generator" content="" />
        <meta name="author" content="" />
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <meta name="robots" content="not follow" />
        <meta name="revisit-after" content="never" />
        <meta name="rating" content="local" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
        <style>
            form {
                width: 250px;
                margin: 20px auto;
            }
            form * {
                display: inline-block;
            }
            form div {
                width: 100%;
                margin: 5px 0px;
            }
            form div label {
                width: 100%;
                text-align: right;
            }
            form div select,
            form div input {
                width: 70%;
                float: right;
            }
            form div select {
                width: 72%;
            }
            form div select option {
                display: block;
            }
            form div input[type="submit"] {
                width: 50%;
                margin: 5px auto;
                float: none;
                display: block;
            }
            span.error {
                display: block;
                color: red;
                width: 250px;
                margin: 20px auto;
            }
            div#prodBox {
                width: 412px;
                display: block;
                margin: 20px auto;
            }
            div#prod {
                width: 100%;
                display: block;
                float: left;
                border: 1px solid silver;
                padding: 10px;
                -webkit-border-radius: 15px 0px;
                   -moz-border-radius: 15px 0px;
                    -ms-border-radius: 15px 0px;
                     -o-border-radius: 15px 0px;
                        border-radius: 15px 0px;
                -webkit-box-shadow: 2px 2px 7px -3px #232f3e;
                   -moz-box-shadow: 2px 2px 7px -3px #232f3e;
                    -ms-box-shadow: 2px 2px 7px -3px #232f3e;
                     -o-box-shadow: 2px 2px 7px -3px #232f3e;
                        box-shadow: 2px 2px 7px -3px #232f3e;
            }
            div#prod span.img {
                width: 150px;
                display: inline-block;
                float: left;
            }
            div#prod span.img img {
                width: 100%;
                height: auto;
                display: inline-block;
                float: left;
                -webkit-border-radius: 15px;
                   -moz-border-radius: 15px;
                    -ms-border-radius: 15px;
                     -o-border-radius: 15px;
                        border-radius: 15px;
                -webkit-box-shadow: 2px 2px 7px -3px #767676;
                   -moz-box-shadow: 2px 2px 7px -3px #767676;
                    -ms-box-shadow: 2px 2px 7px -3px #767676;
                     -o-box-shadow: 2px 2px 7px -3px #767676;
                        box-shadow: 2px 2px 7px -3px #767676;
            }
            div#prod #desc {
                width: 240px;
                display: inline-block;
                float: right;
            }
            div#prod #desc span {
                width: 100%;
                display: block;
                margin: 10px 0px;
            }
            div#prod #desc span.price:before {
                content: "Price: ";
                color: blue;
            }
            div#prod #desc span.title {
                font-weight: bold;
                font-size: 16px;
                color: #141414;
                -webkit-text-shadow: 3px 3px 3px #424242;
                   -moz-text-shadow: 3px 3px 3px #424242;
                    -ms-text-shadow: 3px 3px 3px #424242;
                     -o-text-shadow: 3px 3px 3px #424242;
                        text-shadow: 3px 3px 3px #424242;
            }
        </style>
    </head>
    <body>
        <form action="/testForWork/Amazon.php" method="post">
            <div>
                <label for="asin_id">ASIN:&nbsp;
                    <input type="text" name="asin" id="asin_id" value="<?=str_replace('"','\"',$_REQUEST['asin']);?>" />
                </label>
            </div>
            <div>
                <label for="country_id">Country:&nbsp;
                    <select name="country" id="country_id">
                        <option value="GB"<?= ($_REQUEST['country']=='GB'?' selected="selected"':'');?>>GB</option>
                        <option value="US"<?= ($_REQUEST['country']=='US'?' selected="selected"':'');?>>US</option>
                    </select>
                </label>
            </div>
            <div>
                <label for="send_id">
                    <input type="submit" name="send" id="send_id" value="Send" />
                </label>
            </div>
        </form>
    <?php
    $objAmazon = new Amazon($_REQUEST['country']);
    try {
        $html = $objAmazon->getItem($_REQUEST['asin']);
        echo '
        <div id="prodBox">
            <div id="prod" class="product">
                <span class="img">
                    <a href="'.$objAmazon->strURL.$_REQUEST['asin'].'" target="_blank">
                        <img src="'.$html['imageUrl'][0].'" title="'.str_replace('"', '&#34;', $html['title'][0]).'" alt="'.str_replace('"', '&#34;', $html['title'][0]).'" />
                    </a>
                </span>
                <div id="desc" class="description">
                    <span id="pr" class="price">'.implode(', ', $html['price']).'</span>
                    <span id="tit" class="title">'.$html['title'][0].'</span>
                </div>
            </div>
        </div>';
    } catch (  AmazonException $Ex) {
        echo '<span class="error"><b>error: </b>'.$Ex->__toString().'</spam>';
    }
?>
    </body>
</html>
