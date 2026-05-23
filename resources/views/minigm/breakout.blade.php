<canvas id="breakout" width="480" height="320"></canvas>

<script>
    const canvas = document.getElementById('breakout');
    const ctx = canvas.getContext('2d');

    const paddle = {
        x: canvas.width / 2 - 50,
        y: canvas.height - 20,
        width: 100,
        height: 10,
        speed: 8,
        dx: 0
    };

    const ball = {
        x: canvas.width / 2,
        y: canvas.height / 2,
        radius: 10,
        speed: 4,
        dx: 4,
        dy: -4
    };

    const brick = {
        width: 50,
        height: 20,
        padding: 10,
        offsetX: 45,
        offsetY: 60,
        visible: true
    };

    const bricks = [];
    for (let i = 0; i < 8; i++) {
        for (let j = 0; j < 5; j++) {
            bricks.push({
                x: i * (brick.width + brick.padding) + brick.offsetX,
                y: j * (brick.height + brick.padding) + brick.offsetY,
                ...brick
            });
        }
    }

    function drawPaddle() {
        ctx.fillStyle = 'blue';
        ctx.fillRect(paddle.x, paddle.y, paddle.width, paddle.height);
    }

    function drawBall() {
        ctx.beginPath();
        ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
        ctx.fillStyle = 'red';
        ctx.fill();
        ctx.closePath();
    }

    function drawBricks() {
        bricks.forEach(brick => {
            if (brick.visible) {
                ctx.fillStyle = 'green';
                ctx.fillRect(brick.x, brick.y, brick.width, brick.height);
            }
        });
    }

    function update() {
        // Mover la paleta
        paddle.x += paddle.dx;

        // Mover la pelota
        ball.x += ball.dx;
        ball.y += ball.dy;

        // Detectar colisiones
        if (ball.x + ball.radius > canvas.width || ball.x - ball.radius < 0) {
            ball.dx *= -1;
        }
        if (ball.y - ball.radius < 0) {
            ball.dy *= -1;
        }
        if (ball.y + ball.radius > canvas.height) {
            alert('¡Perdiste!');
            resetGame();
        }

        // Colisión con la paleta
        if (
            ball.y + ball.radius > paddle.y &&
            ball.x > paddle.x &&
            ball.x < paddle.x + paddle.width
        ) {
            ball.dy = -ball.speed;
        }

        // Colisión con los bloques
        bricks.forEach(brick => {
            if (brick.visible) {
                if (
                    ball.x > brick.x &&
                    ball.x < brick.x + brick.width &&
                    ball.y > brick.y &&
                    ball.y < brick.y + brick.height
                ) {
                    ball.dy *= -1;
                    brick.visible = false;
                }
            }
        });

        // Verificar si se ganó
        if (bricks.every(brick => !brick.visible)) {
            alert('¡Ganaste!');
            resetGame();
        }
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawPaddle();
        drawBall();
        drawBricks();
    }

    function gameLoop() {
        update();
        draw();
        requestAnimationFrame(gameLoop);
    }

    function resetGame() {
        ball.x = canvas.width / 2;
        ball.y = canvas.height / 2;
        bricks.forEach(brick => brick.visible = true);
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft') {
            paddle.dx = -paddle.speed;
        } else if (e.key === 'ArrowRight') {
            paddle.dx = paddle.speed;
        }
    });

    document.addEventListener('keyup', () => {
        paddle.dx = 0;
    });

    gameLoop();
</script>