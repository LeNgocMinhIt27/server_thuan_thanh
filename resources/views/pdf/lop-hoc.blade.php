<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>DANH SÁCH SINH VIÊN ĐIỂM DANH</title>
    <style>
        .break_page {
            page-break-after: always;
        }

        .main_title {
            text-align: center;
        }

        #customers {
            padding-top: 16px;
            border-collapse: collapse;
            width: 100%;
        }

        #customers td,
        #customers th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #customers th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            color: black;
        }

        body {
            font-family: DejaVu Sans, sans-serif !important;
        }
    </style>
</head>

<body>
    <h1 class="main_title">DANH S&Aacute;CH SINH VI&Ecirc;N</h1>
    <table style="width:499.5pt;margin-left:-9.0pt;border-collapse:collapse;border:none;">
        <tbody>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Học phần:
                            {{ $subData['ten_hp'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">M&atilde; học phần:
                            {{ $subData['ma_hp'] ?? '' }}</span></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Giảng vi&ecirc;n:
                            {{ $subData['username'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Lớp:
                            {{ $subData['loai'] ?? '' }}</span></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="width: 239.1pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">M&atilde; lớp:
                            {{ $subData['ma'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border: none;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">Địa điểm:
                            {{ $subData['class'] ?? '' }}</span></p>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="width: 239.1pt;border-top: none;border-right: none;border-left: none;border-image: initial;border-bottom: 1pt solid windowtext;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:16px;color:black;">Ng&agrave;y:&nbsp;{{ $subData['date'] ?? '' }}</span></p>
                </td>
                <td colspan="2" style="width: 260.4pt;border-top: none;border-right: none;border-left: none;border-image: initial;border-bottom: 1pt solid windowtext;padding: 0in 5.4pt;vertical-align: top;">
                    <p style='margin:0in;font-size:16px'><span style="font-size:17px">&nbsp;</span></p>
                </td>
            </tr>
        </tbody>
    </table>
    <table id="customers">

        @foreach ($studentData as $group)
            <tr>
                <th style="width:40pt;border:1.5px solid black">STT</th>
                <th style="width:70pt;border:1.5px solid black">MSSV</th>
                <th style="width:140pt;border:1.5px solid black">Họ và tên</th>
                <th style="width:70pt;border:1.5px solid black">Chữ ký</th>
                <th style="width:140pt;border:1.5px solid black">Ghi chú</th>

            </tr>
            @foreach ($group as $student)
                <tr>
                    <td style="border:1.5px solid black">{{ $student['pivot']['stt'] ?? '' }}</td>
                    <td style="border:1.5px solid black">{{ $student['mssv'] ?? '' }}</td>
                    <td style="border:1.5px solid black"></td>
                    <td style="border:1.5px solid black"></td>
                    <td style="border:1.5px solid black"></td>
                </tr>
            @endforeach
        @endforeach

    </table>
</body>

</html>
