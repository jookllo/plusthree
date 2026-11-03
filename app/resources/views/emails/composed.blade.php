<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $email->subject }}</title>
</head>
<body style="margin:0;padding:0;background:#FBF7F0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FBF7F0;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#fffefa;border:1px solid #d9d9d3;">
                <tr>
                    <td style="height:5px;background:#23305C;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="padding:30px 32px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.65;color:#23305C;">
                        {!! $email->safeBody() !!}
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px 26px;border-top:1px solid #d9d9d3;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:#626775;">
                        <strong style="color:#23305C;font-size:14px;">plusthree</strong><br>
                        WHERE AFRICA’S GEMS MEET GLOBAL WORK.<br>
                        <a href="https://plusthree.io" style="color:#B3261E;">plusthree.io</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
