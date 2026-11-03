<div style="font-family:Arial,Helvetica,sans-serif;color:#23305C;">
    <p style="margin:0 0 6px;font-size:12px;color:#626775;">
        To: {{ $email->recipientSummary(5) }}
    </p>
    <h3 style="margin:0 0 18px;font-size:18px;line-height:1.3;">{{ $email->subject }}</h3>

    <div style="font-size:15px;line-height:1.65;">
        {!! $email->safeBody() !!}
    </div>

    <p style="margin:24px 0 0;padding-top:14px;border-top:1px solid #d9d9d3;font-size:12px;line-height:1.6;color:#626775;">
        <strong style="color:#23305C;">plusthree</strong><br>
        WHERE AFRICA’S GEMS MEET GLOBAL WORK.
    </p>
</div>
