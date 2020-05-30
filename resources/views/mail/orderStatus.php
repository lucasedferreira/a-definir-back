<!DOCTYPE html>
<html>
<head>
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <style type="text/css">
        /***********
        Originally based on The MailChimp Reset from Fabio Carneiro, MailChimp User Experience Design
        More info and templates on Github: https://github.com/mailchimp/Email-Blueprints
        http://www.mailchimp.com &amp; http://www.fabio-carneiro.com

        INLINE: Yes.
        ***********/
        /* Client-specific Styles */
        #outlook a {padding: 0;} /* Force Outlook to provide a "view in browser" menu link. */
        body{width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; margin: 0; padding: 0;}
        /* Prevent Webkit and Windows Mobile platforms from changing default font sizes, while not breaking desktop design. */
        .ExternalClass {width: 100%;} /* Force Hotmail to display emails at full width */
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing.  More on that: http://www.emailonacid.com/forum/viewthread/43/ */
        #backgroundTable {margin: 0; padding: 0; width: 100% !important; line-height: 100% !important; background: #ffffff;}
        /* End reset */

        /* Some sensible defaults for images
        1. "-ms-interpolation-mode: bicubic" works to help ie properly resize images in IE. (if you are resizing them using the width and height attributes)
        2. "border:none" removes border when linking images.
        3. Updated the common Gmail/Hotmail image display fix: Gmail and Hotmail unwantedly adds in an extra space below images when using non IE browsers. You may not always want all of your images to be block elements. Apply the "image_fix" class to any image you need to fix.

        Bring inline: Yes.
        */
        img {outline: none; text-decoration: none; -ms-interpolation-mode: bicubic;}
        a img {border: none;}
        .image_fix {display: block;}

        /** Yahoo paragraph fix: removes the proper spacing or the paragraph (p) tag. To correct we set the top/bottom margin to 1em in the head of the document. Simple fix with little effect on other styling. NOTE: It is also common to use two breaks instead of the paragraph tag but I think this way is cleaner and more semantic. NOTE: This example recommends 1em. More info on setting web defaults: http://www.w3.org/TR/CSS21/sample.html or http://meiert.com/en/blog/20070922/user-agent-style-sheets/

        Bring inline: Yes.
        **/
        p {margin: 16px 0;}

        /** Hotmail header color reset: Hotmail replaces your header color styles with a green color on H2, H3, H4, H5, and H6 tags. In this example, the color is reset to black for a non-linked header, blue for a linked header, red for an active header (limited support), and purple for a visited header (limited support).  Replace with your choice of color. The !important is really what is overriding Hotmail's styling. Hotmail also sets the H1 and H2 tags to the same size.

        Bring inline: Yes.
        **/
        h1, h2, h3, h4, h5, h6 {color: #3f3e3e !important;}
        h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: #ea1d2c !important;}

        h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
          color: #ea1d2c !important; /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }

        h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
          color: #ea1d2c !important; /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }

        /** Outlook 07, 10 Padding issue: These "newer" versions of Outlook add some padding around table cells potentially throwing off your perfectly pixeled table.  The issue can cause added space and also throw off borders completely.  Use this fix in your header or inline to safely fix your table woes.

        More info: http://www.ianhoar.com/2008/04/29/outlook-2007-borders-and-1px-padding-on-table-cells/
        http://www.campaignmonitor.com/blog/post/3392/1px-borders-padding-on-table-cells-in-outlook-07/

        H/T @edmelly

        Bring inline: No.
        **/
        table td {border-collapse: collapse;}

        /** Remove spacing around Outlook 07, 10 tables

        More info : http://www.campaignmonitor.com/blog/post/3694/removing-spacing-from-around-tables-in-outlook-2007-and-2010/

        Bring inline: Yes
        **/
        table {border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;}

        /* Styling your links has become much simpler with the new Yahoo.  In fact, it falls in line with the main credo of styling in email, bring your styles inline.  Your link colors will be uniform across clients when brought inline.

        Bring inline: Yes. */
        a {color: #ea1d2c; text-decoration: none !important;}

        /* Or to go the gold star route */
        a:link {color: #ea1d2c;}
        a:visited {color: #ea1d2c;}
        a:hover {color: #ea1d2c;}

        /* MY CSS */
        .wrapper {
          max-width: 650px;
        }

        .sidebar {
          background-color: #eee;
          border-radius: 4px;
          width: 100%;
        }

        .sidebar__label {
          color: #a6a29f;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 15px;
          line-height: 1.4;
          margin: 0 auto;
        }

        .sidebar__value {
          color: #3f3e3e;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 21px;
          font-weight: bolder;
          line-height: 1.2;
          margin: 0 auto;
        }

        .content {
          width: 100% !important;
          margin-bottom: 32px;
        }

        .title {
          color: #3f3e3e;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 26px;
          line-height: 1.2;
          margin-top: 0;
        }

        .title-2 {
          color: #3f3e3e;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 20px;
          font-weight: bolder;
          line-height: 1.2;
          margin: 32px 0 5px;
        }

        .sub-title {
          color: #a6a29f;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 18px;
          line-height: 1.2;
          margin-bottom: 0;
        }

        .summary {
          margin-top: 16px;
        }

        .summary__td {
          padding: 18px 0;
          border-top: 1px solid #f5f0eb;
          border-bottom: 1px solid #f5f0eb;
        }

        .summary__qtd {
          color: #a6a29f;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 18px;
        }

        .summary__value {
          color: #3f3e3e;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 18px;
        }

        .summary__price {
          color: #3f3e3e;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 14px;
        }

        .summary__label {
          color: #a6a29f;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 15px;
          line-height: 1.4;
          margin: 0 0 10px;
        }

        .order {
          display: block;
          background-color: #ea1d2c;
          border-radius: 4px;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 16px;
          line-height: 1;
          margin: 35px 0;
          padding: 16px 0;
          text-align: center;
          width: 100%;
        }

        .dash {
          background: #dcd7d3;
          border: 0;
          border-radius: 2px;
          display: block;
          margin: 15px auto;
          width: 3px;
          height: 22px;
        }

        .footer {
          margin: 25px auto 50px;
          max-width: 640px;
        }

        .slogan {
          color: #3f3e3e;
          font-family: Helvetica, Arial, sans-serif;
          font-size: 20px;
          font-weight: bolder;
          margin: 0 0 20px;
        }

        .markets {
          display: inline-block;
          margin: 0 5px;
        }

        .social {
          display: inline-block;
          margin: 0 15px;
        }

        .copyright {
          color: #a6a29f;
          font-size: 14px;
          line-height: 1.2;
          font-family: Helvetica, Arial, sans-serif;
        }

        /***************************************************
        ****************************************************
        MOBILE TARGETING

        Use @media queries with care.  You should not bring these styles inline -- so it's recommended to apply them AFTER you bring the other stlying inline.

        Note: test carefully with Yahoo.
        Note 2: Don't bring anything below this line inline.
        ****************************************************
        ***************************************************/

        /* NOTE: To properly use @media queries and play nice with yahoo mail, use attribute selectors in place of class, id declarations.
        table[class=classname]
        Read more: http://www.campaignmonitor.com/blog/post/3457/media-query-issues-in-yahoo-mail-mobile-email/
        */
        @media only screen and (min-width:500px) {
          .content {
            float: right !important;
            width: 62% !important;
          }

          .content__td {
            padding-left: 45px !important;
          }

          .sidebar {
            width: 38% !important;
          }

          .title {
            font-size: 30px !important;
          }

          .order {
            width: 245px !important;
          }

          .footer {
            border-top: 1px solid #f5f0eb !important;
          }

          .slogan {
            display: inline-block !important;
            margin-right: 10px !important;
          }
        }
  </style>

    <!-- Targeting Windows Mobile -->
    <!--[if IEMobile 7]>
    <style type="text/css">

    </style>
    <![endif]-->

    <!-- ***********************************************
    ****************************************************
    END MOBILE TARGETING
    ****************************************************
    ************************************************ -->

    <!--[if gte mso 9]>
    <style>
    /* Target Outlook 2007 and 2010 */
    </style>
    <![endif]-->

    <title>confirma&ccedil;&atilde;o do pedido</title>
</head>
<body>
<!-- Wrapper/Container Table: Use a wrapper table to control the width and the background color consistently of your email. Use this approach instead of setting attributes on the body tag. -->
<table cellpadding="0" cellspacing="0" border="0" id="backgroundTable">
    <tr>
        <td>

            
            <!-- Header -->
            <a href="http://www.ifood.com.br/delivery/?utm_source=Confirmacao&utm_medium=Emailsistema&utm_campaign=Blogo" rel="external" target="_blank" style="text-decoration: none;">
                <img src="https://s3.amazonaws.com/static.ifood.com.br/img/email/logo_ifood.png" alt="iFood" height="48" style="margin: 50px auto;" class="image_fix">
            </a>

            <!-- Tables are the most common way to format your email consistently. Set your table widths inside cells and in most cases reset cellpadding, cellspacing, and border to zero. Use nested tables as a way to space effectively in your message. -->
            <table width="95%" cellpadding="0" cellspacing="0" border="0" align="center" class="wrapper">
                <tr>
                    <td valign="top">

                        <!-- content -->
                        <table width="62%" cellpadding="0" cellspacing="0" border="0" class="content">
                            <tr>
                                <td valign="top" class="content__td">
                                    <h1 class="title">Pedido confirmado</h1>

                                    <p class="sub-title">
                                        <?php echo $order['client']['name'] ?>, seu pedido já está sendo preparado.
                                    </p>

                                </td>
                            </tr>
                            <tr>
                                <td valign="top" class="content__td">

                                    
                                    <h2 class="title-2" style="margin-top: 10px;">
                                        <?php echo $order['restaurant']['name'] ?>
                                    </h2>
                                    
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="summary">
                                            <tr>
                                                <td class="summary__td">
                                                    <span class="summary__qtd">1</span>
                                                    <span class="summary__value">Frango Catupiry   G</span>
                                                </td>
                                                <td align="right" class="summary__td">
                                                    <span class="summary__price">R$ 14,00</span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="summary__td">
                                                    <span class="summary__qtd">1</span>
                                                    <span class="summary__value">Coca Cola 2l</span>
                                                </td>
                                                <td align="right" class="summary__td">
                                                    <span class="summary__price">R$ 9,00</span>
                                                </td>
                                            </tr>
                                        
                                        <tr>
                                            <td style="border-bottom: 0;" class="summary__td">
                                                <p class="summary__label">Taxa de entrega</p>
                                                <span class="summary__value">Total</span>
                                            </td>
                                            <td style="border-bottom: 0;" align="right" class="summary__td">
                                                <p class="summary__label">R$ 0,00</p>
                                                <span class="summary__value">R$ 13,00</span>
                                            </td>
                                        </tr>
                                    </table>

                                    <h2 class="title-2">Endere&ccedil;o de entrega:</h2>

                                    <p class="summary__label">
                                        R Sete De Setembro
                                    </p>

                                    <h2 class="title-2">Forma de pagamento </h2>
                                    <p class="summary__label">MASTERCARD</p>

                                </td>
                            </tr>
                        </table>

                        <!-- sidebar -->
                        <table width="38%" cellpadding="0" cellspacing="0" border="0" class="sidebar">
                            <tr>
                                <td align="center" style="padding-top: 32px;">
                                    <img src="https://s3.amazonaws.com/static.ifood.com.br/img/email/icon-checked@2x.png" alt="" width="48" height="48" class="image_fix">
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <hr class="dash" />
                                </td>
                            </tr>
                            
                            <tr>
                                <td align="center">
                                    <p class="sidebar__label" style="font-size: 16px; margin-bottom: 6px;">
                                        Pedido:
                                    </p>
                                    <p class="sidebar__value">
                                        # <?php echo $order['details']['order_no'] ?>
                                    </p>
                                    <p class="sidebar__label">
                                        20/12/2018 &#x2022; 18:40
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <hr class="dash" />
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <p class="sidebar__value">
                                        50 min                                    
                                    </p>
                                    <p class="sidebar__label">
                                        Previs&atilde;o de entrega                                    
                                    </p>
                                </td>
                            </tr>
                            <br>
                            <br>
                        </table>

                    </td>
                </tr>
            </table>

            
            <!-- footer -->
            <table width="95%" cellpadding="0" cellspacing="0" border="0" align="center" class="footer">
                <tr>
                    <td align="center" style="border-top: 1px solid #f5f0eb; padding: 20px 0;">
                        <p class="copyright">
                            Pedido Processado Pelo <a href="https://multipedidos.com.br" rel="external" target="_blank" style="color: #ea1d2c; text-decoration: none;">Sistema Multipedidos</a>
                            <br />
                            Este &eacute; um e-mail autom&aacute;tico. Em caso de d&uacute;vidas, entre em contato com o estabecimento
                        </p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
<!-- End of wrapper table -->
</body>
</html>