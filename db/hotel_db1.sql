<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trio's Villa - Use Case Diagram Generator</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px;
        }
        .btn {
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn:hover { background-color: #1e293b; }
        canvas {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <button class="btn" onclick="downloadPNG()">Download as PNG</button>
    <canvas id="diagramCanvas" width="900" height="950"></canvas>

    <script>
        const canvas = document.getElementById('diagramCanvas');
        const ctx = canvas.getContext('2d');

        // Background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // System Boundary Box
        const boxX = 220;
        const boxY = 40;
        const boxWidth = 640;
        const boxHeight = 870;

        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 2;
        ctx.strokeRect(boxX, boxY, boxWidth, boxHeight);

        // System Boundary Title
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText("Trio's Villa Resort Management System", boxX + (boxWidth / 2), boxY + 30);

        // Actor: Admin (Stick Figure)
        const actorHeadX = 90;
        const actorHeadY = 380;
        const headRadius = 24;

        ctx.lineWidth = 2;
        ctx.beginPath();
        // Head
        ctx.arc(actorHeadX, actorHeadY, headRadius, 0, Math.PI * 2);
        // Body
        ctx.moveTo(actorHeadX, actorHeadY + headRadius);
        ctx.lineTo(actorHeadX, actorHeadY + 110);
        // Arms
        ctx.moveTo(actorHeadX - 45, actorHeadY + 50);
        ctx.lineTo(actorHeadX + 45, actorHeadY + 50);
        // Left Leg
        ctx.moveTo(actorHeadX, actorHeadY + 110);
        ctx.lineTo(actorHeadX - 35, actorHeadY + 190);
        // Right Leg
        ctx.moveTo(actorHeadX, actorHeadY + 110);
        ctx.lineTo(actorHeadX + 35, actorHeadY + 190);
        ctx.stroke();

        // Actor Label
        ctx.font = 'bold 20px Arial';
        ctx.fillText("Admin", actorHeadX, actorHeadY + 225);

        // Connection Source Point (From stick figure right arm out to system)
        const originX = actorHeadX + 45;
        const originY = actorHeadY + 50;

        // Use Cases
        const useCases = [
            "Admin Login / Authentication",
            "Manage Room Types",
            "Manage Rooms & Media",
            "Manage Bookings & Calendar",
            "Housekeeping & Room Status",
            "Manage Payments & Billing",
            "Logout"
        ];

        const ovalCenterX = 680;
        const ovalRadiusX = 145;
        const ovalRadiusY = 32;
        const startY = 120;
        const spacingY = 110;

        // Line extending from actor to boundary threshold
        const meetX = boxX + 60;
        const meetY = originY;

        ctx.beginPath();
        ctx.moveTo(originX, originY);
        ctx.lineTo(meetX, meetY);
        ctx.stroke();

        useCases.forEach((text, index) => {
            const currentY = startY + (index * spacingY);

            // Draw Oval
            ctx.beginPath();
            ctx.ellipse(ovalCenterX, currentY, ovalRadiusX, ovalRadiusY, 0, 0, Math.PI * 2);
            ctx.fillStyle = '#ffffff';
            ctx.fill();
            ctx.stroke();

            // Text inside Oval
            ctx.fillStyle = '#000000';
            ctx.font = '14px Arial';
            ctx.fillText(text, ovalCenterX, currentY + 5);

            // Target arrow attachment point on the left edge of the ellipse
            const targetX = ovalCenterX - ovalRadiusX;
            const targetY = currentY;

            // Connector Line
            ctx.beginPath();
            ctx.moveTo(meetX, meetY);
            ctx.lineTo(targetX, targetY);
            ctx.stroke();

            // Open UML Arrowhead
            const angle = Math.atan2(targetY - meetY, targetX - meetX);
            const arrowLength = 16;
            const arrowAngle = Math.PI / 7;

            ctx.beginPath();
            ctx.moveTo(targetX, targetY);
            ctx.lineTo(
                targetX - arrowLength * Math.cos(angle - arrowAngle),
                targetY - arrowLength * Math.sin(angle - arrowAngle)
            );
            ctx.moveTo(targetX, targetY);
            ctx.lineTo(
                targetX - arrowLength * Math.cos(angle + arrowAngle),
                targetY - arrowLength * Math.sin(angle + arrowAngle)
            );
            ctx.stroke();
        });

        function downloadPNG() {
            const link = document.createElement('a');
            link.download = 'trios_villa_admin_use_case_diagram.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    </script>
</body>
</html>