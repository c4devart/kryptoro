<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PayBear Payment</title>
    <script type="text/javascript" src="assets/js/paybear.js"></script>
    <link rel="stylesheet" type="text/css" href="assets/css/paybear.css" />

</head>
<body>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('lib/CmsOrder.php');

$cms_order = new CmsOrder();
$last_order_id = '100001';

$last_order = $cms_order->findByIncrementId($last_order_id);

if (empty($last_order)) {
    echo 'The order not found';
    return;
}

if (($last_order) && ($last_order->status == 'Complete')) {

    echo 'The order is paid';
    return;
}

?>
<div style="padding: 20px">

    <table>
        <tr>
            <td><label for="fiat_value">Order Id</label></td>
            <td><input style="background-color:#CCC; opacity: 0.8;" type="text" value="<?php echo $last_order->increment_id ?>" id="orderId" readonly></td>
        </tr>
        <tr>
            <td><label for="fiat_value">Order Total</label></td>
            <td><input style="background-color:#CCC; opacity: 0.8;" type="text" value="<?php echo $last_order->order_total ?>" id="fiatValue" readonly></td>
        </tr>
        <tr>
            <td><label for="fiat_value">Fiat Sign</label></td>
            <td><input style="background-color:#CCC; opacity: 0.8;" type="text" value="<?php echo $last_order->fiat_sign ?>" id="fiatSign" readonly></td>
        </tr>
        <tr>
            <td><label for="fiat_value">Fiat Currency</label></td>
            <td><input style="background-color:#CCC; opacity: 0.8;"  type="text" value="<?php echo $last_order->fiat_currency ?>" id="fiatCurrency" readonly></td>
        </tr>
        <tr>
            <td style="padding: 20px 0"><button id="paybear-all">Pay with Crypto</button></td>
        </tr>
    </table>



</div>
<div id="paybear" style="display: none">
    <div class="p30 PayBear-spinner" style="display: none;">
        <p>Loading payment details...</p>
        <div class="PayBear-spinner__container"><span class="PayBear-spinner__item"></span></div>
    </div>

    <div class="p30 PayBear-app-error" style="display: none;">
        This payment method is temporarily unavailable. Please try again in a minute.
        <button class="P-btn P-btn-grey">Retry</button>
    </div>
    <!-- PayBear widget-->
    <div class="PayBear-app" style="display: none;">
        <div class="PayBear-container">
            <div class="PayBear">
                <!-- PayBear header -->
                <div class="PayBear__Nav">
                    <a href="/" class="PayBear__Nav__arrow" style="display: none;">
                        <svg height="22" width="22" viewBox="0 0 22 22" xmlns="http://www.w3.org/2000/svg">
                            <path d="m15.605355 8.8542609h-13.7616838c-1.01823148 0-1.8436712.82344-1.8436712 1.8392051 0 1.015764.82543972 1.839205 1.8436712 1.839205h14.3762408l-2.483628 2.47761c-.719998.718254-.719998 1.882774 0 2.601028.719999.718255 1.887347.718255 2.607345 0l5.116373-5.103976c.412815-.411816.588939-.970336.528375-1.507333.06056-.536997-.11556-1.0955171-.528375-1.5073331l-5.116373-5.1039766c-.719998-.718254-1.887346-.718254-2.607345 0-.719998.718255-.719998 1.8827736 0 2.6010286z"></path>
                        </svg>
                    </a>
                    <div class="PayBear__brand-link" style="display: none;">
                        <span>
                            Powered by <a href="https://www.paybear.io" target="_blank" rel="noopener noreferrer">
                            PayBear.io<svg height="14" viewBox="0 0 14 14" width="14" xmlns="http://www.w3.org/2000/svg"
                                           class="PayBear__Nav__external">
                            <path d="m333.875 38c-.482562 0-.875-.392-.875-.875v-12.25c0-.483.392438-.875.875-.875h5.6875c.241938 0 .4375.1955625.4375.4375v.875c0 .2419375-.195562.4375-.4375.4375h-4.8125v10.5h10.5v-4.8125c0-.2419375.195562-.4375.4375-.4375h.875c.241938 0 .4375.1955625.4375.4375v5.6875c0 .483-.392438.875-.875.875zm12.690554-14c .240249 0 .434446.1933285.434446.4344461v4.3444615c0 .1746474-.10557.3340891-.268053.4005594-.053872.0230256-.11035.0338868-.166393.0338868-.112956 0-.224174-.0443135-.307154-.1272927l-1.557489-1.5579239-4.344027 4.3444615c-.169869.1698684-.444438.1698684-.615176 0l-.614307-.6143069c-.169868-.1698684-.169868-.4444384 0-.6143068l4.344462-4.3444615-1.557924-1.5579239c-.124252-.1246861-.161614-.3114979-.094275-.4735463.066905-.1629173.225912-.2680533.401428-.2680533z"
                                  fill="#b9b9b9" transform="translate(-333 -24)"></path>
                            </svg></a>
                        </span>
                    </div>
                </div>
                <!-- Select currency -->
                <div class="PayBear__Icons"></div>
                <!-- Content -->
                <div class="P-Payment P-box" style="display: none;">
                    <!-- Header of content-->
                    <div class="P-Payment__header" id="payment-header">
                        <div class="P-Payment__header__timer">--</div>
                        <div class="P-Payment__header__check" style="display: none;"></div>
                        <div class="P-Payment__header__text">
                            <div class="P-Payment__header__title">Waiting on Payment</div>
                            <div class="P-Payment__header__helper">---</div>
                        </div>
                    </div>
                    <div class="P-box__inner">
                        <!-- First payment step -->
                        <div class="P-Payment__start">
                            <div class="P-Payment__value">
                                <div class="P-Payment__value__icon">
                                    <img src="">
                                </div>
                                <div>
                                    <div class="P-Payment__value__pay"><span class="P-Payment__value__coins"></span></div>
                                    <div class="P-Payment__value__price" style="display: none;">---</div>
                                </div>
                            </div>
                            <div class="P-Tabs" id="paybear-tabs">
                                <ul class="P-Tabs__Tab-list P-Tabs__Tab-list--second">
                                    <li role="tab" class="P-Tabs__Tab P-Tabs__Tab--wallet">Wallet</li>
                                    <li role="tab" class="P-Tabs__Tab P-Tabs__Tab--selected">Copy</li>
                                    <li role="tab" class="P-Tabs__Tab">Scan</li>
                                </ul>
                                <div class="P-Payment__address">
                                    <div class="P-Payment__address__text">---</div>
                                    <code>---</code>
                                </div>
                                <div class="P-Tabs__Tab-panel P-Tabs__Tab-panel--wallet">
                                    <div>
                                        <a href="#" class="P-btn-block P-wallet-btn">
                                            <span class="P-btn-block__inner">
                                                <i class="P-wallet-icon"></i>
                                                <span class="P-btn-block__text">Open in Wallet</span>
                                            </span>
                                        </a>
                                    </div>
                                </div>

                                <div class="P-Tabs__Tab-panel P-Tabs__Tab-panel--selected">
                                    <div class="P-buttons-flex">
                                        <a class="P-btn-block P-btn-copy-address">
                                            <span class="P-btn-block__copied-text">
                                                <svg height="11" viewBox="0 0 13 11" width="13" xmlns="http://www.w3.org/2000/svg"><path d="m4.42775177 7.85005539 5.97311463-5.97311463c.5359766-.53597656 1.4049663-.53597656 1.9409428 0 .5359766.53597657.5359766 1.40496628 0 1.94094285l-6.93193869 6.93193869c-.5029721.5029721-1.29920739.5339442-1.83829672.0929164-.06892267-.0472373-.13449273-.1014683-.19571746-.1626931l-2.71766557-2.71766553c-.53597656-.53597657-.53597656-1.40496628 0-1.94094285.53597657-.53597656 1.40496628-.53597656 1.94094285 0z" fill-rule="evenodd" fill="#4FBDA6" transform="translate(0 -1)"/></svg>
                                                Copied to Clipboard!
                                            </span>
                                            <span class="P-btn-block__inner">
                                                <span class="P-btn-block__helper">--</span>
                                                Copy Address
                                            </span>
                                        </a>
                                        <a class="P-btn-block P-Payment__value__copy">
                                            <span class="P-btn-block__copied-text">
                                                <svg height="11" viewBox="0 0 13 11" width="13" xmlns="http://www.w3.org/2000/svg"><path d="m4.42775177 7.85005539 5.97311463-5.97311463c.5359766-.53597656 1.4049663-.53597656 1.9409428 0 .5359766.53597657.5359766 1.40496628 0 1.94094285l-6.93193869 6.93193869c-.5029721.5029721-1.29920739.5339442-1.83829672.0929164-.06892267-.0472373-.13449273-.1014683-.19571746-.1626931l-2.71766557-2.71766553c-.53597656-.53597657-.53597656-1.40496628 0-1.94094285.53597657-.53597656 1.40496628-.53597656 1.94094285 0z" fill-rule="evenodd" fill="#4FBDA6" transform="translate(0 -1)"/></svg>
                                                Copied to Clipboard!
                                            </span>
                                            <span class="P-btn-block__inner">
                                                <span class="P-btn-block__helper">--</span>
                                                Copy Amount
                                            </span>
                                        </a>
                                    </div>
                                </div>

                                <div class="P-Tabs__Tab-panel">
                                    <div class="P-Payment__qr">
                                        <img src="">
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- Payment confirming step -->
                        <div class="P-Payment__unpaid" style="display: none;">
                            <div class="P-Content">
                                <div class="P-Content__icon">
                                    <img src="data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjI1OC45OTgyIiB2aWV3Qm94PSIwIDAgMjk4LjMyNTEzIDI1OC45OTgyIiB3aWR0aD0iMjk4LjMyNTEzIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Im0yNzguNzc0MTkgMjU4Ljk5ODJoLTI1OS41NDgzODNjLTEwLjYxODA1MyAwLTE5LjIyNTgwNy04LjU4ODk3LTE5LjIyNTgwNy0xOS4xODUxOCAwLTIuOTQ4NTMgMC0xNC44MTM2MiAxLTEwLjgxMzYyIDIuMzQ0NTEwMiA5LjM3ODA0IDEwLjU2MjUyNS0xOC4zNzE1NyAxOC4yMjU4MDctMTguMzcxNTdoMjU5LjU0ODM4M2M4LjMzMzA1IDAgMTYuNTU4MDggNi4zMjIxMyAxOS4yMjU4MSAxNi4zNzE1Ny43MzE1MiAyLjc1NTY2IDAgMTAuNTMzNDQgMCAxMi44MTM2MiAwIDEwLjU5NjIxLTguNjA3NzUgMTkuMTg1MTgtMTkuMjI1ODEgMTkuMTg1MTh6IiBmaWxsPSIjY2Q0MTJkIi8+PHBhdGggZD0ibTEzNC42MDQwOCA5LjYzMzk2MWM4LjI2NzY5LTEyLjM2NTQ1MSAxOS45MTU1My0xMy4zMTYzMTcgMjguODA1NjYgMGwxMjkuNTk0NTUgMjA2LjEwODgzOWM5LjYwMzI5IDE0LjM3MDMxIDQuNzk2ODQgMjQuMDcwMjItOS42MDI2OSAyNC4wNzAyMmgtMjY4LjgwNTYwOGMtMTQuNDAwNzI5OSAwLTE5LjIwMjM3NTEtOS43MDExMS05LjYwMDg4NzItMjQuMDcwMjJ6IiBmaWxsPSIjZjA1ZjUwIi8+PHBhdGggZD0ibTEzOC43MDEzIDE2MS45ODc4MWgxNy41NTQ0N2MyLjY4NTAzIDAgNC44OTA3LTIuMTIwNjggNC45OTYxNC00LjgwMzY0bDMuMzQwMTYtODQuOTg4NDA5Yy4xMDg0NC0yLjc1OTMtMi4wNDA1LTUuMDg0MDYtNC43OTk3OS01LjE5MjUtLjA2NTQtLjAwMy0uMTMwODgtLjAwNC0uMTk2MzUtLjAwNGgtMjQuMjM0NzhjLTIuNzYxNDMgMC01IDIuMjM4NTgtNSA1IDAgLjA2NTUuMDAxLjEzMDk0LjAwNC4xOTYzNmwzLjM0MDE2IDg0Ljk4ODQwOWMuMTA1NDQgMi42ODI5NiAyLjMxMTExIDQuODAzNjQgNC45OTYxNCA0LjgwMzY0em0tMTEuMDI5MyAzMC40ODc1NWMwIDExLjE5OTUxIDguOTE4MTMgMTkuMjg4MDQgMTkuNDk1NDQgMTkuMjg4MDQgMTAuNzg0NzEgMCAxOS45MTAyMy04LjA4ODUzIDE5LjkxMDIzLTE5LjI4ODA0IDAtMTAuOTkyMTEtOS4xMjU1Mi0xOC44NzMyNC0xOS45MTAyMy0xOC44NzMyNC0xMC41NzczMSAwLTE5LjQ5NTQ0IDcuODgxMTMtMTkuNDk1NDQgMTguODczMjR6IiBmaWxsPSIjZmZmIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=">
                                </div>
                                <h2>Oops. You underpaid by <span class="P-Payment__unpaid__underpaid">---</span>!</h2>
                                <div class="P-Payment__unpaid__block">
                                    <div>
                                        <div>DUE</div>
                                        <span class="P-Payment__unpaid__due">---</span>
                                    </div>
                                    <div>
                                        <div>YOU PAID</div>
                                        <span class="P-Payment__unpaid__paid">---</span>
                                    </div>
                                    <div>
                                        <div>UNDERPAID</div>
                                        <span class="P-Payment__unpaid__underpaid">---</span>
                                    </div>
                                </div>
                                <p>But don’t worry, we credited <span class="P-Payment__unpaid__paid">---</span> (<span class="P-Payment__unpaid__paidFiat">---</span>) to your LeoList giftcard.</p>
                                <div>
                                    <a href="#" class="P-btn P-btn--primary">
                                        Continue
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Payment expired step -->
                        <div class="P-Payment__expired" style="display: none;">
                            <div class="P-Content">
                                <p>
                                    <strong>Your payment window has expired.</strong>
                                    Hit the Refresh button below to lock in
                                    a new exchange rate and start again.
                                </p>
                                <div>
                                    <a href="/" class="P-btn">
                                        <i class="P-btn__icon P-btn__icon--refresh-white"></i>
                                        Refresh
                                    </a>
                                </div>
                                <button class="P-Payment__helper">What's a payment window?</button>
                            </div>
                        </div>
                        <!-- Payment expired step: Help screen -->
                        <div class="P-Payment__expired-helper" style="display: none;">
                            <div class="P-Content">
                                <div class="P-Content__icon">
                                    <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48c3ZnIHdpZHRoPSI1OHB4IiBoZWlnaHQ9IjY0cHgiIHZpZXdCb3g9IjAgMCA1OCA2NCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIj4gICAgICAgIDx0aXRsZT5pZl9kYXRlLXRpbWUtc3RvcHdhdGNoLXRpbWVyLTE1c18xMTA0MjE2PC90aXRsZT4gICAgPGRlc2M+Q3JlYXRlZCB3aXRoIFNrZXRjaC48L2Rlc2M+ICAgIDxkZWZzPjwvZGVmcz4gICAgPGcgaWQ9IuKAlC1JY29uIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4gICAgICAgIDxnIGlkPSJUaWVyLTEtLy1JY29uLS8tU3RvcHdhdGNoLUV4cGlyZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTMuMDAwMDAwLCAwLjAwMDAwMCkiIGZpbGwtcnVsZT0ibm9uemVybyI+ICAgICAgICAgICAgPGcgaWQ9ImlmX2RhdGUtdGltZS1zdG9wd2F0Y2gtdGltZXItMTVzXzExMDQyMTYiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDMuMDAwMDAwLCAwLjAwMDAwMCkiPiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNNTQuNTc0NzA4OCwxOS4xMTQxMDUzIEw1Ny43NTA1Njg0LDE1LjkzODM1NzkgTDQ4LjIyMzEwMTgsNi40MTA4OTEyMyBMNDUuMDQ3MzU0NCw5LjU4Njc1MDg4IEw0Ni42MzUyMjgxLDExLjE3NDYyNDYgTDQzLjE3NzU0MzksMTQuNjMyMzA4OCBDMzkuOTMzODY2NywxMi4zMjI5MTkzIDM2LjE2MTM0NzQsMTAuNzI1Mjc3MiAzMi4wNzE3NDc0LDEwLjAzNzU1NzkgTDMyLjA3MTc0NzQsNS4xNDIzNDM4NiBMMzQuMzE3MzYxNCw1LjE0MjM0Mzg2IEwzNC4zMTczNjE0LDAuNjUxMTE1Nzg5IEwyMC44NDM2NzcyLDAuNjUxMTE1Nzg5IEwyMC44NDM2NzcyLDUuMTQyMzQzODYgTDIzLjA4OTI5MTIsNS4xNDIzNDM4NiBMMjMuMDg5MjkxMiwxMC4wMzc2NzAyIEMxMC4zNDkyNDkxLDEyLjE3OTc2MTQgMC42MzMxNTA4NzcsMjMuMjMyIDAuNjMzMTUwODc3LDM2LjU4MDk0MDQgQzAuNjMzMTUwODc3LDUxLjQ2MzYzNTEgMTIuNjk3ODI0Niw2My41MjgzMDg4IDI3LjU4MDUxOTMsNjMuNTI4MzA4OCBDNDIuNDYzMjE0LDYzLjUyODMwODggNTQuNTI3ODg3Nyw1MS40NjM2MzUxIDU0LjUyNzg4NzcsMzYuNTgwOTQwNCBDNTQuNTI3ODg3NywzMC43NjIxMDUzIDUyLjY2NDkyNjMsMjUuMzg4NDYzMiA0OS41MjkxNTA5LDIwLjk4MzkxNTggTDUyLjk4NjgzNTEsMTcuNTI2MjMxNiBMNTQuNTc0NzA4OCwxOS4xMTQxMDUzIFoiIGlkPSJTaGFwZSIgZmlsbD0iI0NERDdERSI+PC9wYXRoPiAgICAgICAgICAgICAgICA8cGF0aCBkPSJNMjcuNTgwNTE5Myw5LjYzMzU3MTkzIEMyOS4xMTQyNzM3LDkuNjMzNTcxOTMgMzAuNjA4MDU2MSw5Ljc5MTQzODYgMzIuMDcxNzQ3NCwxMC4wMzc2NzAyIEwzMi4wNzE3NDc0LDUuMTY3MjcwMTggTDMyLjA3MTc0NzQsNS4xNDIzNDM4NiBMMjMuMDg5MjkxMiw1LjE0MjM0Mzg2IEwyMy4wODkyOTEyLDYuMjY1MTUwODggTDI3LjU4MDUxOTMsNi4yNjUxNTA4OCBMMjcuNTgwNTE5Myw5LjYzMzU3MTkzIEwyNy41ODA1MTkzLDkuNjMzNTcxOTMgWiIgaWQ9IlNoYXBlIiBmaWxsPSIjQjhDNUNGIj48L3BhdGg+ICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik00Ni42MzUyMjgxLDE3LjUyNjIzMTYgQzQ3LjY5ODUyNjMsMTguNTg5NTI5OCA0OC42NTQ3MDg4LDE5Ljc1NTY3NzIgNDkuNTI5MTUwOSwyMC45ODM5MTU4IEw1Mi45NjkwOTQ3LDE3LjU0Mzk3MTkgTDUyLjk4NjcyMjgsMTcuNTI2MzQzOSBMNDYuNjM1MTE1OCwxMS4xNzQ3MzY4IEw0NS44NDEwNjY3LDExLjk2ODc4NiBMNDkuMDE2OTI2MywxNS4xNDQ1MzMzIEw0Ni42MzUyMjgxLDE3LjUyNjIzMTYgWiIgaWQ9IlNoYXBlIiBmaWxsPSIjQjhDNUNGIj48L3BhdGg+ICAgICAgICAgICAgICAgIDxwYXRoIGQ9Ik0yNy41MjMxNDM5LDU0LjU0NTg1MjYgQzE3LjYxNzI5MTIsNTQuNTQ1ODUyNiA5LjU1ODIzMTU4LDQ2LjQ4Njc5MyA5LjU1ODIzMTU4LDM2LjU4MDk0MDQgQzkuNTU4MjMxNTgsMjYuNjc1MDg3NyAxNy42MTcyOTEyLDE4LjYxNjAyODEgMjcuNTIzMTQzOSwxOC42MTYwMjgxIEMzNy40Mjg5OTY1LDE4LjYxNjAyODEgNDUuNDg4MDU2MSwyNi42NzUwODc3IDQ1LjQ4ODA1NjEsMzYuNTgwOTQwNCBDNDUuNDg4MDU2MSw0Ni40ODY3OTMgMzcuNDI4OTk2NSw1NC41NDU4NTI2IDI3LjUyMzE0MzksNTQuNTQ1ODUyNiBaIiBpZD0iU2hhcGUiIGZpbGw9IiNGRkZGRkYiPjwvcGF0aD4gICAgICAgICAgICAgICAgPHBhdGggZD0iTTI4LjIzNjgyODEsMzYuNTgzMjk4MiBDMjguMjM0MTMzMywyNy45Mzg0NzAyIDIxLjIwMDE5NjUsMjAuOTAxMjc3MiAxMi41NTQ4MDcsMjAuOTAxMjc3MiBMMTIuNTU0ODA3LDM2LjU3ODQ3MDIgTDI4LjIzNjgyODEsMzYuNTgzMjk4MiBaIiBpZD0iU2hhcGUiIGZpbGw9IiNFN0I2QjEiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIwLjM5NTgxOCwgMjguNzQyMjg4KSBzY2FsZSgtMSwgMSkgdHJhbnNsYXRlKC0yMC4zOTU4MTgsIC0yOC43NDIyODgpICI+PC9wYXRoPiAgICAgICAgICAgICAgICA8ZyBpZD0iR3JvdXAiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIyLjUwMDAwMCwgMzYuNTAwMDAwKSBzY2FsZSgtMSwgMSkgdHJhbnNsYXRlKC0yMi41MDAwMDAsIC0zNi41MDAwMDApIHRyYW5zbGF0ZSgxMy4wMDAwMDAsIDMyLjAwMDAwMCkiPiAgICAgICAgICAgICAgICAgICAgPHBhdGggZD0iTTQuNTM4OTc1NDQsMC40OTU4MzE1NzkgQzIuMzA4Mjk0NzQsMC40OTU4MzE1NzkgMC40OTY4NzAxNzUsMi4zMDcyNTYxNCAwLjQ5Njg3MDE3NSw0LjUzNzkzNjg0IEMwLjQ5Njg3MDE3NSw2Ljc3MzY3MDE4IDIuMzA4Mjk0NzQsOC41ODAwNDIxMSA0LjUzODk3NTQ0LDguNTgwMDQyMTEgQzQuOTA0Nzg1OTYsOC41ODAwNDIxMSA1LjI1ODAyMTA1LDguNTMxNzYxNCA1LjU5MDgyMTA1LDguNDQyODM1MDkgTDE4LjAxMTg3MzcsNS4yMzQwNzcxOSBDMTguNzMwOTE5Myw1LjA0ODU4OTQ3IDE4LjczMDkxOTMsNC4wMjcyODQyMSAxOC4wMTE4NzM3LDMuODQxNzk2NDkgTDUuNTkwODIxMDUsMC42MzgwOTEyMjggQzUuMjU1NDM4NiwwLjU0NjU4MjQ1NiA0LjkwNDc4NTk2LDAuNDk1ODMxNTc5IDQuNTM4OTc1NDQsMC40OTU4MzE1NzkgWiIgaWQ9IlNoYXBlIiBmaWxsPSIjRDY0MjMwIj48L3BhdGg+ICAgICAgICAgICAgICAgICAgICA8Y2lyY2xlIGlkPSJPdmFsIiBmaWxsPSIjQTkzNTJBIiBjeD0iNC41OTk5NDM4NiIgY3k9IjQuNTM1MjQyMTEiIHI9IjEuNjg0MjEwNTMiPjwvY2lyY2xlPiAgICAgICAgICAgICAgICA8L2c+ICAgICAgICAgICAgPC9nPiAgICAgICAgPC9nPiAgICA8L2c+PC9zdmc+">
                                </div>
                                <h2>What's a Payment Window?</h2>
                                <p>
                                    A payment window is a pre-set amount of time-usually 15 mins-where the
                                    exchange rate is locked in to ensure a fair rate for customer and
                                    merchant.
                                </p>
                                <div>
                                    <button class="P-btn P-btn-grey">
                                        <i class="P-btn__icon P-btn__icon--back-grey"></i>
                                        Got it!
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Payment confirming step -->
                        <div class="P-Payment__confirming" style="display: none;">
                            <div class="P-Content">
                                <div class="Confirming__icon">
                                    <svg height="159" viewBox="0 0 255 159" width="255" class="Confirming__pic">

                                        <defs id="svg-Group2-wq2YP3q">
                                            <ellipse id="svg-Group2-1RCLVi9" cx="16.93353" cy="16.953842" rx="16.743816"
                                                     ry="16.748526"></ellipse>
                                            <path id="svg-Group2-3qWaf6Y"
                                                  d="m19.1094113 19.4515481c-.0479576 0-.0954574-.0209934-.1277233-.061378l-2.0300388-2.5383115c-.0231779-.029005-.0358293-.0650078-.0358293-.1021551v-3.5533875c0-.090285.0731949-.1635005.1634545-.1635005.0902595 0 .1634544.0732155.1634544.1635005v3.4960643l1.9942423 2.493545c.0563918.0705014.04495.1734086-.0255316.2297835-.0301736.0241327-.066199.0358393-.1020282.0358393zm-2.0320984 5.671176c-2.2361875 0-4.3385386-.8710976-5.9197969-2.4527681-1.58125831-1.5817032-2.45207821-3.6846784-2.45207821-5.9214951 0-1.164025.23426291-2.2907391.69628324-3.3488158.44626333-1.0219758 1.08298377-1.9325424 1.89250827-2.7063245.0652837-.0623918.1687177-.0600374.2310919.005232.0623742.0653021.0600205.1687978-.0052306.2311569-1.60423996 1.5334705-2.48774392 3.5999525-2.48774392 5.8187514 0 4.4372709 3.60894342 8.0472623 8.04496622 8.0472623 4.4360229 0 8.0450317-3.6099587 8.0450317-8.0472623 0-4.4372709-3.6089761-8.04722958-8.0450317-8.04722958-.6261286 0-1.2496746.07220179-1.8533118.21457796-.0878732.02060106-.175877-.03374648-.1966031-.12161162-.0206933-.08789783.033737-.17592646.1215775-.19665831.6281881-.14813139 1.2769715-.22327619 1.9283374-.22327619 2.2362203 0 4.3386041.87106489 5.9198297 2.45276804 1.5812583 1.5817032 2.4521109 3.6846457 2.4521109 5.9214624s-.8708526 4.3397919-2.4521109 5.9214951c-1.5812256 1.5816378-3.6836094 2.4527354-5.9198297 2.4527354z"
                                                  stroke="#e5e5e5" stroke-width="1.4"></path>
                                            <path id="svg-Group2-3smKeov"
                                                  d="m5.90126337 11.8227744-2.21088172-2.21088175c-.84422585-.84422585-2.2129864-.84422585-3.05721226 0-.84422585.84422585-.84422585 2.21298645 0 3.05721225l3.61123548 3.6112355c.09643612.0964361.19971665.1818563.30827792.2562606.84912883.6946705 2.10329265.6458858 2.8955326-.1463541l10.91861521-10.91861525c.8442259-.84422585.8442259-2.2129864 0-3.05721226-.8442258-.84422585-2.2129864-.84422585-3.0572123 0z"></path>
                                            <filter id="svg-Group2-3RRrZIi" color-interpolation-filters="sRGB">
                                                <feFlood flood-opacity=".08" result="flood"
                                                         id="svg-Group2-20Qef8x"></feFlood>
                                                <feComposite in="flood" in2="SourceGraphic" operator="in"
                                                             result="composite1" id="svg-Group2-1gR8TY7"></feComposite>
                                                <feGaussianBlur in="composite1" result="blur" stdDeviation="3"
                                                                id="svg-Group2-3m4KQYT"></feGaussianBlur>
                                                <feOffset dx="1" dy="0" result="offset"
                                                          id="svg-Group2-3H8OZbG"></feOffset>
                                                <feComposite in="SourceGraphic" in2="offset" operator="over"
                                                             result="composite2" id="svg-Group2-mlwIIR2"></feComposite>
                                            </filter>
                                            <filter id="svg-Group2-2c3jl78" color-interpolation-filters="sRGB">
                                                <feFlood flood-opacity=".08" result="flood"
                                                         id="svg-Group2-2pfCQw0"></feFlood>
                                                <feComposite in="flood" in2="SourceGraphic" operator="in"
                                                             result="composite1" id="svg-Group2-20lf6-e"></feComposite>
                                                <feGaussianBlur in="composite1" result="blur" stdDeviation="99"
                                                                id="svg-Group2-1T0A3gr"></feGaussianBlur>
                                                <feOffset dx="0" dy="0" result="offset"
                                                          id="svg-Group2-16kt3IZ"></feOffset>
                                                <feComposite in="SourceGraphic" in2="offset" operator="over"
                                                             result="composite2" id="svg-Group2-3CcF-dL"></feComposite>
                                            </filter>
                                        </defs>
                                        <g fill="none" fill-rule="evenodd" id="svg-Group2-2VvHRvC">
                                            <g stroke="#e7e7e7" stroke-width="3"
                                               transform="translate(1.429195 14.485123)" id="svg-Group2-316PS5E">
                                                <g id="svg-Group2-1Se2Wzu" transform="translate(0,20)">
                                                    <path id="svg-Group2-15k4HcN" class="Confirming__pic__point--top"
                                                          d="M 51.940975,-3.6429195 15.163168,73.7002"></path>
                                                    <path id="svg-Group2-14SZJkh"
                                                          d="M 15.3316,75.131874 119.18953,95.88984"></path>
                                                    <path id="svg-Group2-2JlcBzH"
                                                          d="M 121.09512,95.88984 224.68903,25.802895"></path>
                                                    <path id="svg-Group2-2jCsKLw" class="Confirming__pic__point--top"
                                                          d="M 223.02164,24.850099 53.793729,-4.8219449"></path>
                                                </g>
                                                <path d="m17.264 94.288 208.496-49.136" id="svg-Group2-SPc06lX"></path>
                                                <path d="m53.12 17.928 65.072 100.264"
                                                      class="Confirming__pic__point--top"
                                                      id="svg-Group2-1cCDDsZ"></path>
                                            </g>
                                            <g id="svg-Group2-3j52qep">

                                                <g class="Confirming__pic__point" filter="url(#svg-Group2-3RRrZIi)"
                                                   transform="translate(-.094857 91.297632)" id="svg-Group2-2pZ8H5m">
                                                    <use class="circle" fill="#fff" filter="url(#svg-Group2-2c3jl78)"
                                                         height="100%" transform="translate(-.094857)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-1RCLVi9" id="svg-Group2-1jbqLbQ"></use>
                                                    <use class="time" fill="#eee" height="100%" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3qWaf6Y" id="svg-Group2-1RPlxfi"></use>
                                                    <use class="check" fill="#fff" height="100%"
                                                         transform="translate(7.338674 7.551509)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3smKeov" id="svg-Group2--WIG81x"></use>
                                                </g>
                                                <g class="Confirming__pic__point" filter="url(#svg-Group2-3RRrZIi)"
                                                   transform="translate(102.21398 112.91314)" id="svg-Group2-23YW68n">
                                                    <use class="circle" fill="#fff" filter="url(#svg-Group2-2c3jl78)"
                                                         height="100%" transform="translate(-.094857)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-1RCLVi9" id="svg-Group2-1K0JjZO"></use>
                                                    <use class="time" fill="#eee" height="100%" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3qWaf6Y" id="svg-Group2-3IuRduH"></use>
                                                    <use class="check" fill="#fff" height="100%"
                                                         transform="translate(7.338674 7.551509)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3smKeov" id="svg-Group2-zjo63Vt"></use>
                                                </g>
                                                <g class="Confirming__pic__point" filter="url(#svg-Group2-3RRrZIi)"
                                                   transform="translate(206.30508 43.182199)" id="svg-Group2-2e5hs45">
                                                    <use class="circle" fill="#fff" filter="url(#svg-Group2-2c3jl78)"
                                                         height="100%" transform="translate(-.094857)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-1RCLVi9" id="svg-Group2-3gntfDR"></use>
                                                    <use class="time" fill="#eee" height="100%" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3qWaf6Y" id="svg-Group2-3aAQ2uI"></use>
                                                    <use class="check" fill="#fff" height="100%"
                                                         transform="translate(7.338674 7.551509)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3smKeov" id="svg-Group2-1bs4HhS"></use>
                                                </g>
                                                <g class="Confirming__pic__point Confirming__pic__point--top"
                                                   filter="url(#svg-Group2-3RRrZIi)"
                                                   transform="translate(36.71822 15.559321)" id="svg-Group2-23TNxss">
                                                    <use class="circle" fill="#fff" filter="url(#svg-Group2-2c3jl78)"
                                                         height="100%" transform="translate(-.094857)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-1RCLVi9" id="svg-Group2-RMIL9eH"></use>
                                                    <use class="time" fill="#eee" height="100%" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3qWaf6Y" id="svg-Group2-3IbT0Fm"></use>
                                                    <use class="check" fill="#fff" height="100%"
                                                         transform="translate(7.338674 7.551509)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3smKeov" id="svg-Group2-2D5EIq7"></use>
                                                </g>
                                                <g class="Confirming__pic__point Confirming__pic__point--center"
                                                   transform="translate(76.830504,73.911013)" id="svg-Group2-Bbnsjlm"
                                                   filter="url(#svg-Group2-3RRrZIi)">
                                                    <use class="circle" fill="#fff" filter="url(#svg-Group2-2c3jl78)"
                                                         height="100%" transform="translate(-0.094857,0)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-1RCLVi9"></use>
                                                    <use class="time" height="100%" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3qWaf6Y" id="svg-Group2-QFaQSla"
                                                         fill="#eee" x="0" y="0"></use>
                                                    <use class="check" height="100%"
                                                         transform="translate(7.338674,7.551509)" width="100%"
                                                         xmlns:xlink="http://www.w3.org/1999/xlink"
                                                         xlink:href="#svg-Group2-3smKeov" id="svg-Group2-VkZrAFL"
                                                         fill="#fff" x="0" y="0"></use>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                </div>
                                <h2 class="P-confirmations">---</h2>
                                <p>
                                    <strong>Your order will be processed soon.</strong>
                                    You can leave this tab open until your payment is confirmed,
                                    <strong>or</strong> close it and wait for a confirmation email.
                                </p>
                                <div>
                                    <button class="P-btn P-btn--sm">
                                        <i class="P-btn__icon--close"></i>
                                        Close
                                    </button>
                                </div>
                                <button class="P-Payment__helper">What does this mean?</button>
                            </div>
                        </div>
                        <!-- Payment confirming step: Help screen -->
                        <div class="P-Payment__confirming-helper" style="display: none;">
                            <div class="P-Content">
                                <div class="P-Content__icon">
                                    <img src="data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjI4MCIgdmlld0JveD0iMCAwIDI1NCAyODAiIHdpZHRoPSIyNTQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJub256ZXJvIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMi42ODc1IC0yKSI+PHBhdGggZD0ibTI0Mi4xNzUyNyA4NC44MTg4NDIxIDE0LjA5Mjg3Ny0xNC4wOTIzNzg5LTQyLjI3ODEzMy00Mi4yNzgxMzM0LTE0LjA5MjM3OSAxNC4wOTI4NzcyIDcuMDQ2MTkgNy4wNDYxODk1LTE1LjM0MzQ3NCAxNS4zNDM0NzM3Yy0xNC4zOTM4MTgtMTAuMjQ3OTE1OC0zMS4xMzQzNzItMTcuMzM3NDUyNy00OS4yODE5NzItMjAuMzg5MjA3di0yMS43MjI1MTIzaDkuOTY0OTEydi0xOS45Mjk4MjQ1OGgtNTkuNzg5NDczNXYxOS45Mjk4MjQ1OGg5Ljk2NDkxMjV2MjEuNzIzMDEwNWMtNTYuNTMzOTM3IDkuNTA1NTI5OC05OS42NDkxMjI5OCA1OC41NDk4Mzg2LTk5LjY0OTEyMjk4IDExNy43ODU3NjE2IDAgNjYuMDQxOTU4IDUzLjUzNjk4OTQ4IDExOS41Nzg5NDcgMTE5LjU3ODk0Njk4IDExOS41Nzg5NDcgNjYuMDQxOTU4IDAgMTE5LjU3ODk0OC01My41MzY5ODkgMTE5LjU3ODk0OC0xMTkuNTc4OTQ3IDAtMjUuODIxMDgxLTguMjY2ODkxLTQ5LjY2NjYxOC0yMi4xODE4OTUtNjkuMjExNzk2N2wxNS4zNDM0NzQtMTUuMzQzNDczN3oiIGZpbGw9IiNjZGQ3ZGUiLz48cGF0aCBkPSJtMTIyLjM4ODU1NCA0Mi43NDg5NzU0YzYuODA2MDM1IDAgMTMuNDM0Njk1LjcwMDUzMzQgMTkuOTI5ODI1IDEuNzkzMTg2di0yMS42MTI0LS4xMTA2MTA1aC0zOS44NTk2NDl2NC45ODI0NTYxaDE5LjkyOTgyNHoiIGZpbGw9IiNiOGM1Y2YiLz48cGF0aCBkPSJtMjA2Ljk0MzgyNSA3Ny43NzI2NTI2YzQuNzE4Mzg2IDQuNzE4Mzg2IDguOTYxNDQ1IDkuODkzMTY0OSAxMi44NDE3ODIgMTUuMzQzNDczN2wxNS4yNjQ3NTEtMTUuMjY0NzUwOS4wNzgyMjQtLjA3ODIyNDUtMjguMTg1MjU2LTI4LjE4NTI1NjItMy41MjM1OTMgMy41MjM1OTMgMTQuMDkyODc4IDE0LjA5MjM3OXoiIGZpbGw9IiNiOGM1Y2YiLz48cGF0aCBkPSJtMTIyLjEzMzk1MSAyNDIuMDQ3MjIxYy00My45NTcyMjEyIDAtNzkuNzE5Mjk4NC0zNS43NjIwNzctNzkuNzE5Mjk4NC03OS43MTkyOThzMzUuNzYyMDc3Mi03OS43MTkyOTg0IDc5LjcxOTI5ODQtNzkuNzE5Mjk4NGM0My45NTcyMjEgMCA3OS43MTkyOTggMzUuNzYyMDc3NCA3OS43MTkyOTggNzkuNzE5Mjk4NHMtMzUuNzYyMDc3IDc5LjcxOTI5OC03OS43MTkyOTggNzkuNzE5Mjk4eiIgZmlsbD0iI2ZmZiIvPjxwYXRoIGQ9Im0xOTEuODYzNDI1IDE2Mi4zMzgzODZjLS4wMTE5NTgtMzguMzYxNDI1LTMxLjIyNTA1My02OS41ODg5Njg1LTY5LjU4ODk2OS02OS41ODg5Njg1djY5LjU2NzU0MzV6IiBmaWxsPSIjZDVlMmYyIi8+PHBhdGggZD0ibTEyMi4yMDQyMDQgMTQ0LjIwMDI1M2MtOS44OTg2NDYgMC0xNy45MzY4NDMgOC4wMzgxOTYtMTcuOTM2ODQzIDE3LjkzNjg0MiAwIDkuOTIxMDY2IDguMDM4MTk3IDE3LjkzNjg0MiAxNy45MzY4NDMgMTcuOTM2ODQyIDEuNjIzMjg0IDAgMy4xOTA3NjQtLjIxNDI0NiA0LjY2NzU2NC0uNjA4ODU2bDU1LjExODQyMS0xNC4yMzg4NjNjMy4xOTA3NjUtLjgyMzEwMiAzLjE5MDc2NS01LjM1NTE0NCAwLTYuMTc4MjQ2bC01NS4xMTg0MjEtMTQuMjE2NDQyYy0xLjQ4ODI1OS0uNDA2MDctMy4wNDQyOC0uNjMxMjc3LTQuNjY3NTY0LS42MzEyNzd6IiBmaWxsPSIjZDY0MjMwIi8+PGNpcmNsZSBjeD0iMTIyLjQ3NDc1MSIgY3k9IjE2Mi4xMjUxMzciIGZpbGw9IiNhOTM1MmEiIHI9IjcuNDczNjg0Ii8+PC9nPjwvc3ZnPg==">
                                </div>
                                <h2>Why do I have to wait?</h2>
                                <ul>
                                    <li>Crypto payments need to be confirmed by several computers on the
                                        network, known as the blockchain.
                                    </li>
                                    <li>Payments usually fully confirm within a few minutes. Or up to an hour in
                                        extreme cases
                                    </li>
                                    <li>Don't worry, your <strong>payment will be processed automatically even
                                            if you close this window</strong>.
                                    </li>
                                    <li class="block-explorer-li" style="display: none;">You can track your payment's
                                        progress with a <a class="P-block-explorer" target="_blank" href="#">block
                                            explorer
                                            <svg height="14" viewBox="0 0 14 14" width="14"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path d="m333.875 38c-.482562 0-.875-.392-.875-.875v-12.25c0-.483.392438-.875.875-.875h5.6875c.241938 0 .4375.1955625.4375.4375v.875c0 .2419375-.195562.4375-.4375.4375h-4.8125v10.5h10.5v-4.8125c0-.2419375.195562-.4375.4375-.4375h.875c.241938 0 .4375.1955625.4375.4375v5.6875c0 .483-.392438.875-.875.875zm12.690554-14c .240249 0 .434446.1933285.434446.4344461v4.3444615c0 .1746474-.10557.3340891-.268053.4005594-.053872.0230256-.11035.0338868-.166393.0338868-.112956 0-.224174-.0443135-.307154-.1272927l-1.557489-1.5579239-4.344027 4.3444615c-.169869.1698684-.444438.1698684-.615176 0l-.614307-.6143069c-.169868-.1698684-.169868-.4444384 0-.6143068l4.344462-4.3444615-1.557924-1.5579239c-.124252-.1246861-.161614-.3114979-.094275-.4735463.066905-.1629173.225912-.2680533.401428-.2680533z"
                                                      fill="#b9b9b9" transform="translate(-333 -24)"></path>
                                            </svg>
                                        </a>
                                    </li>
                                    <li>If for any reason your payment fails to confirm, your money will return
                                        to your wallet automatically.
                                    </li>
                                </ul>
                                <div>
                                    <button class="P-btn P-btn-grey">
                                        <i class="P-btn__icon P-btn__icon--back-grey"></i>
                                        Got it!
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Payment confirmed step -->
                        <div class="P-Payment__confirmed" style="display: none;">
                            <div class="P-Content">
                                <div class="P-Content__icon">
                                    <img src="data:image/svg+xml;base64,PHN2ZyBoZWlnaHQ9IjI2MCIgdmlld0JveD0iMCAwIDI2MyAyNjAiIHdpZHRoPSIyNjMiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgdHJhbnNmb3JtPSIiPjxlbGxpcHNlIGN4PSIxMzAuMTk4MDIiIGN5PSIxMzAiIGZpbGw9IiMzZGIzOWUiIHJ4PSIxMzAuMTk4MDIiIHJ5PSIxMzAiLz48cGF0aCBkPSJtMjU0LjAwMzMxNyA4OS43NTcyYy0zLjY4NzIwOC0xMS4zMTc4LTguODc0Mjk3LTIxLjk1MTgtMTUuMzQ1MTM5LTMxLjY2OGwtMTEzLjk2NzUzNCAxMTguNTIzNi4yMjkxNDggMTMuMTg5OCA1LjM3OTc4Mi0uMDMzOHoiIGZpbGw9IiMzN2ExOGUiLz48cGF0aCBkPSJtMjYwLjU5Mzk0MSA1MS4xNzMyLTIzLjIxNjkxMS0yMi45MDM0Yy0zLjE2OTAyLTMuMTI3OC04LjMwOTIzOC0zLjEyNzgtMTEuNDc4MjU4IDBsLTk5LjI0NDc0MiAxMDIuMDcwOC00Mi43OTg2OTM0LTQyLjIyNGMtMy4xNzQyMjc3LTMuMTI3OC04LjMwOTIzNzYtMy4xMjc4LTExLjQ4MDg2MTQgMGwtMjAuNTc5MDk5IDIwLjMwMzRjLTMuMTY5MDE5OCAzLjEyNTItMy4xNjkwMTk4IDguMTk1MiAwIDExLjMyMDRsNjguNjg5ODcxOCA2Ny43NTg2YzEuODMwNTg0IDEuODA3IDQuMzEyMTU4IDIuNTM1IDYuNjk3Mzg2IDIuMjU0MiAyLjM4NTIyNy4yNzgyIDQuODY2ODAyLS40NDcyIDYuNjk3Mzg2LTIuMjU0MmwxMjYuNzEzOTIxLTEyNS4wMDI4YzMuMTY5MDE5LTMuMTI3OCAzLjE2OTAxOS04LjE5NTIgMC0xMS4zMjN6IiBmaWxsPSIjZjhmOGY4Ii8+PHBhdGggZD0ibTEzMy44ODAwMiAxODcuNDk5IDEyNi43MTM5MjEtMTI1LjAwMjhjMy4xNjkwMTktMy4xMjc4IDMuMTY5MDE5LTguMTk1MiAwLTExLjMyM2wtMy43ODg3NjMtMy43Mzg4LTEzMC4zNDY0NDUgMTI4LjAxMS03MS43ODU5ODA1LTY5Ljg2NzItMi44NzQ3NzIzIDIuODM5MmMtMy4xNjkwMTk4IDMuMTI1Mi0zLjE2OTAxOTggOC4xOTUyIDAgMTEuMzIwNGw2OC42ODcyNjc4IDY3Ljc2MTJjMS44MzA1ODQgMS44MDcgNC4zMTIxNTggMi41MzUgNi42OTczODYgMi4yNTQyIDIuMzg1MjI3LjI4MDggNC44NjY4MDItLjQ0NDYgNi42OTczODYtMi4yNTQyeiIgZmlsbD0iI2ViZWJlYiIvPjwvZz48L3N2Zz4=">
                                </div>
                                <div class="P-Payment__confirmed__title">Payment Processed!</div>
                                <h2>Thank You For Your Purchase!</h2>
                                <p>
                                    Redirecting you back in 5 seconds.
                                </p>
                                <div>
                                    <a href="#" class="P-btn P-btn-grey">
                                        <i class="P-btn__icon P-btn__icon--refresh"></i>
                                        Redirect Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var fiatValue = document.getElementById('fiatValue').value;
        var fiatCurrency = document.getElementById('fiatCurrency').value;
        var fiatSign = document.getElementById('fiatSign').value;
        var order_id = document.getElementById('orderId').value;

        window.paybear = new Paybear({
            button: '#paybear-all',
            fiatValue: fiatValue,
            currencies: "currencies.php?order_id=" + order_id,
            statusUrl: "status.php?order_id=" + order_id,
            fiatCurrency: fiatCurrency,
            fiatSign: fiatSign,
            minOverpaymentFiat: 1,
            maxUnderpaymentFiat: 0.1,
            enablePoweredBy: true,
            timer: 15*60,
            modal: true
        });
    })();
</script>
