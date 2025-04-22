<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(to right, #4b0082, #9400d3);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        
        .content {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            text-align: center;
            color: #666;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(to right, #4b0082, #9400d3);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Master Magical Key to the Universe</h1>
    </div>
    
    <div class="content">
        
        {!! $content !!}

        <!-- Replace any buttons with email-friendly ones -->
        <div style="margin-top: 25px; margin-bottom: 25px; text-align: center;">
            <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://www.mastermagicalkey.com" style="height:40px;v-text-anchor:middle;width:200px;" arcsize="10%" stroke="f" fillcolor="#4b0082">
                <w:anchorlock/>
                <center>
            <![endif]-->
            <a href="https://mastermagicalkey.com/chapters" target="_blank"
                style="background-color:#4b0082;background-image:linear-gradient(to right, #4b0082, #9400d3);border-radius:4px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:14px;font-weight:bold;line-height:40px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">Explore</a>
            <!--[if mso]>
                </center>
                </v:roundrect>
            <![endif]-->
        </div>               
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Master Magical Key to the Universe. All rights reserved.</p>
        </div>
    </div>
</body>
</html>