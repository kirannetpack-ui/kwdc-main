@if($isBirthday)
<div id="birthdayOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; pointer-events: none; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.35); backdrop-filter: blur(6px);">
    <div style="background: white; border-radius: 28px; padding: 40px 50px; max-width: 520px; text-align: center; box-shadow: 0 40px 100px rgba(0,0,0,0.5); pointer-events: auto; animation: popIn 0.6s ease; position: relative; overflow: hidden;">
        
        <!-- Decorative top bar -->
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 8px; background: linear-gradient(90deg, #f59e0b, #d97706, #ef4444, #f59e0b);"></div>

        <div style="font-size: 80px; margin-bottom: 10px; animation: bounce 2s infinite;">
            🎂
        </div>
        <div style="font-size: 40px; margin-bottom: 6px; letter-spacing: 4px;">
            🎈🎉🎈
        </div>
        <h1 style="font-size: 34px; font-weight: 800; color: #1e3c72; margin: 10px 0 6px;">
            Happy Birthday!
        </h1>
        <p style="font-size: 24px; font-weight: 700; color: #f59e0b; margin: 0;">
            {{ $birthdayMessage }}
        </p>
        <p style="font-size: 15px; color: #4b5563; margin: 20px 0 10px; line-height: 1.6;">
            🎯 Thank you for being an essential part of the <strong>KTM-WDC</strong> family. 
            Your contribution and trust drive our success. We wish you a year filled with 
            joy, prosperity, and countless achievements!
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <span style="background: #fef3c7; padding: 6px 14px; border-radius: 20px; font-size: 13px; color: #92400e;">💪 Valued Partner</span>
            <span style="background: #dbeafe; padding: 6px 14px; border-radius: 20px; font-size: 13px; color: #1e40af;">⭐ Trusted Member</span>
            <span style="background: #d1fae5; padding: 6px 14px; border-radius: 20px; font-size: 13px; color: #065f46;">🤝 Team Spirit</span>
        </div>
        <button onclick="closeBirthday()" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 14px 40px; border-radius: 40px; font-weight: 700; font-size: 16px; cursor: pointer; margin-top: 18px; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);">
            ❤️ Thank You
        </button>
    </div>
</div>

<!-- Confetti Canvas -->
<canvas id="confettiCanvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9998; pointer-events: none;"></canvas>

<style>
    @keyframes popIn {
        0% { transform: scale(0.5) rotate(-5deg); opacity: 0; }
        100% { transform: scale(1) rotate(0deg); opacity: 1; }
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    @media (max-width: 600px) {
        #birthdayOverlay > div { padding: 25px 20px; margin: 15px; }
        #birthdayOverlay h1 { font-size: 26px; }
        #birthdayOverlay p { font-size: 18px; }
        #birthdayOverlay .fa-5x { font-size: 60px; }
    }
</style>

<script>
    (function() {
        const canvas = document.getElementById('confettiCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const colors = ['#f59e0b', '#d97706', '#ef4444', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#f472b6', '#fbbf24', '#34d399'];
        const pieces = [];
        for (let i = 0; i < 180; i++) {
            pieces.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height - canvas.height,
                width: Math.random() * 12 + 4,
                height: Math.random() * 7 + 3,
                color: colors[Math.floor(Math.random() * colors.length)],
                speed: Math.random() * 4 + 2,
                rotation: 0,
                rotationSpeed: (Math.random() - 0.5) * 0.06,
                drift: (Math.random() - 0.5) * 0.5,
            });
        }

        let frameId = null;

        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let active = false;
            for (let p of pieces) {
                p.y += p.speed;
                p.x += p.drift;
                p.rotation += p.rotationSpeed;
                if (p.y < canvas.height + 30) active = true;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rotation);
                ctx.globalAlpha = Math.min(1, (canvas.height - p.y + 50) / 100);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.width/2, -p.height/2, p.width, p.height);
                ctx.restore();
            }
            if (!active) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                return;
            }
            frameId = requestAnimationFrame(draw);
        }
        draw();

        window.addEventListener('resize', function() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        // Clean up on close
        window.closeBirthday = function() {
            const overlay = document.getElementById('birthdayOverlay');
            if (overlay) overlay.style.display = 'none';
            if (canvas) canvas.style.display = 'none';
            if (frameId) cancelAnimationFrame(frameId);
        };
    })();
</script>
@endif