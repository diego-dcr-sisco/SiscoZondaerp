@extends('layouts.app') @section('content')
    @if (!auth()->check())
        <?php
        header('Location: /login');
        exit(); ?>
    @endif

    <style>
        canvas {
            display: block;
            margin: 0 auto;
            background-color: #000;
        }
    </style>

    <body>
        <canvas id="gameCanvas" width="400" height="400"></canvas>
        <script>
            const canvas = document.getElementById("gameCanvas");
            const ctx = canvas.getContext("2d");

            const gridSize = 20;
            const tileCount = canvas.width / gridSize;

            let snake = [{
                x: 10,
                y: 10
            }];
            let food = {
                x: 5,
                y: 5
            };
            let direction = {
                x: 0,
                y: 0
            };
            let score = 0;

            function gameLoop() {
                update();
                draw();
                setTimeout(gameLoop, 100);
            }

            function update() {
                const head = {
                    x: snake[0].x + direction.x,
                    y: snake[0].y + direction.y
                };

                // Colisión con los bordes
                if (head.x < 0 || head.x >= tileCount || head.y < 0 || head.y >= tileCount) {
                    resetGame();
                    return;
                }

                // Colisión consigo misma
                if (snake.some(segment => segment.x === head.x && segment.y === head.y)) {
                    resetGame();
                    return;
                }

                snake.unshift(head);

                // Comer la comida
                if (head.x === food.x && head.y === food.y) {
                    score++;
                    food = {
                        x: Math.floor(Math.random() * tileCount),
                        y: Math.floor(Math.random() * tileCount)
                    };
                } else {
                    snake.pop();
                }
            }

            function draw() {
                ctx.fillStyle = "black";
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                ctx.fillStyle = "lime";
                snake.forEach(segment => ctx.fillRect(segment.x * gridSize, segment.y * gridSize, gridSize, gridSize));

                ctx.fillStyle = "red";
                ctx.fillRect(food.x * gridSize, food.y * gridSize, gridSize, gridSize);

                ctx.fillStyle = "white";
                ctx.fillText(`Score: ${score}`, 10, 20);
            }

            function resetGame() {
                snake = [{
                    x: 10,
                    y: 10
                }];
                direction = {
                    x: 0,
                    y: 0
                };
                score = 0;
            }

            window.addEventListener("keydown", e => {
                switch (e.key) {
                    case "ArrowUp":
                        if (direction.y === 0) direction = {
                            x: 0,
                            y: -1
                        };
                        break;
                    case "ArrowDown":
                        if (direction.y === 0) direction = {
                            x: 0,
                            y: 1
                        };
                        break;
                    case "ArrowLeft":
                        if (direction.x === 0) direction = {
                            x: -1,
                            y: 0
                        };
                        break;
                    case "ArrowRight":
                        if (direction.x === 0) direction = {
                            x: 1,
                            y: 0
                        };
                        break;
                }
            });

            gameLoop();
        </script>
    @endsection
