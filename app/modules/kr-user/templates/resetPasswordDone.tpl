<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta charset="utf-8">
    <title>{{ SUBJECT }} - {{ APP_TITLE }}</title>
    <style media="screen">
      html { margin: 0; padding: 0; }
      body {
        background: #f5f7fa;
        font-size: 16px; font-family: sans-serif;
        margin: 0; padding: 0;
        display: flex; justify-content: center;
        padding: 25px 0px;
        color:#181f2c;
      }
      h1, h2, h3, h4 { margin: 0; padding: 0; }
      h3 { font-size: 25px; margin-bottom: 10px; }
      h4 { font-size: 19px; margin-bottom: 15px; }
      table { background: #fff; border-radius: 2px; max-width: 94vw; width: 550px; box-shadow: 0px 3px 7px 0px #00000014; }
      tr#kr-email-header > td { text-align: center; padding: 15px 0px; }
      tr#kr-email-header > td > img { width: 220px; max-width: 90%; }
      tr#kr-email-maintext > td, #kr-email-footertext > td { padding: 15px; }
      tr#kr-support > td { text-align: center; padding: 25px 0px; }
      tr#kr-support > td > label { font-weight: bold; text-transform: uppercase; font-size: 18px; }
      tr#kr-support > td > span { font-weight: bold; text-transform: uppercase; font-size: 20px; color:#ff7700; }
      p { line-height: 21px; margin: 0; padding: 0; }
    </style>
  </head>
  <body>
    <table>
      <tr id="kr-email-header">
        <td><img src="{{ APP_URL }}{{ LOGO_BLACK }}" title="{{ APP_TITLE }}"/></td>
      </tr>
      <tr id="kr-email-maintext">
        <td>
          <h3>Hi, {{ USER_NAME }} !</h3>
          <h4>Your password has been changed.</h4>
          <p>If you did not make this request, please contact the support :</p>
        </td>
      </tr>
      <tr id="kr-support">
        <td>
          <label>Email</label><br/>
          <span>{{ SUPPORT_EMAIL }}</span>
        </td>
      </tr>
      <tr id="kr-email-footertext">
        <td>
          <p>
          Love,<br/>
          {{ APP_TITLE }}
          </p>
        </td>
      </tr>
    </table>
  </body>
</html>
