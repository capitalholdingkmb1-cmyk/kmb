<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#151515">
    <meta name="description" content="Capital Holding KMB — Notre site est actuellement en cours de mise à jour.">
    <title>Mise à jour | Capital Holding KMB</title>

    <style>
        :root {
            --ink: #171717;
            --ink-soft: #242321;
            --gold: #c99b50;
            --gold-light: #e5c68e;
            --paper: #f5f1e9;
            --white: #fffdf9;
            --muted: #77736b;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-width: 320px;
            background: var(--paper);
        }

        body {
            min-height: 100vh;
            min-height: 100svh;
            margin: 0;
            overflow-x: hidden;
            color: var(--ink);
            background:
                radial-gradient(circle at 8% 5%, rgba(201, 155, 80, .18), transparent 28rem),
                radial-gradient(circle at 93% 85%, rgba(201, 155, 80, .12), transparent 30rem),
                var(--paper);
            font-family: Inter, Avenir, "Segoe UI", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            opacity: .45;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.045'/%3E%3C/svg%3E");
        }

        .page {
            width: min(1420px, 100%);
            min-height: 100vh;
            min-height: 100svh;
            margin: 0 auto;
            padding: clamp(22px, 4vw, 56px);
            display: grid;
            grid-template-rows: auto 1fr auto;
            gap: 28px;
        }

        .topbar,
        .footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .logo {
            display: block;
            width: clamp(220px, 25vw, 338px);
            height: auto;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            border: 1px solid rgba(23, 23, 23, .12);
            border-radius: 999px;
            color: #57534d;
            background: rgba(255, 253, 249, .52);
            font-size: 11px;
            font-weight: 750;
            letter-spacing: .14em;
            text-transform: uppercase;
            backdrop-filter: blur(10px);
        }

        .status-dot {
            position: relative;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 0 5px rgba(201, 155, 80, .14);
        }

        .status-dot::after {
            content: "";
            position: absolute;
            inset: -5px;
            border: 1px solid var(--gold);
            border-radius: inherit;
            animation: pulse 2s ease-out infinite;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(380px, .75fr);
            align-items: center;
            gap: clamp(38px, 7vw, 110px);
            padding: clamp(28px, 5vw, 76px) 0;
        }

        .content {
            width: 100%;
            min-width: 0;
            max-width: 760px;
            animation: reveal .9s .1s both;
        }

        .eyebrow {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 0 0 22px;
            color: #9f7434;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            content: "";
            width: 42px;
            height: 1px;
            background: var(--gold);
        }

        h1 {
            max-width: 720px;
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(56px, 8.1vw, 118px);
            font-weight: 400;
            letter-spacing: -.065em;
            line-height: .87;
        }

        h1 span {
            display: block;
            margin-left: clamp(25px, 6vw, 88px);
            color: var(--gold);
            font-style: italic;
        }

        .intro {
            max-width: 570px;
            margin: clamp(28px, 4vw, 48px) 0 0;
            color: var(--muted);
            font-size: clamp(16px, 1.35vw, 19px);
            line-height: 1.75;
        }

        .intro strong {
            color: var(--ink);
            font-weight: 650;
        }

        .visual {
            position: relative;
            width: min(100%, 510px);
            aspect-ratio: 1;
            justify-self: end;
            display: grid;
            place-items: center;
            isolation: isolate;
            animation: reveal 1s .25s both;
        }

        .orbit {
            position: absolute;
            inset: 4%;
            border: 1px solid rgba(23, 23, 23, .12);
            border-radius: 50%;
            animation: rotate 20s linear infinite;
        }

        .orbit::before,
        .orbit::after {
            content: "";
            position: absolute;
            border-radius: 50%;
        }

        .orbit::before {
            top: 16%;
            right: 4.8%;
            width: 11px;
            height: 11px;
            background: var(--gold);
            box-shadow: 0 0 0 10px rgba(201, 155, 80, .12);
        }

        .orbit::after {
            bottom: 7%;
            left: 18%;
            width: 6px;
            height: 6px;
            background: var(--ink);
        }

        .orbit-inner {
            position: absolute;
            inset: 15%;
            border: 1px dashed rgba(201, 155, 80, .36);
            border-radius: 50%;
            animation: rotate 28s linear infinite reverse;
        }

        .orbit-inner::before {
            content: "";
            position: absolute;
            top: 50%;
            left: -4px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold);
        }

        .core {
            position: relative;
            width: 51%;
            aspect-ratio: 1;
            overflow: hidden;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: var(--white);
            background: var(--ink);
            box-shadow: 0 28px 70px rgba(23, 23, 23, .22);
        }

        .core::before {
            content: "";
            position: absolute;
            width: 150%;
            height: 44%;
            background: linear-gradient(90deg, transparent, rgba(229, 198, 142, .24), transparent);
            transform: rotate(-35deg) translateY(-190%);
            animation: shine 4s 1s ease-in-out infinite;
        }

        .monogram {
            position: relative;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(42px, 6vw, 82px);
            letter-spacing: -.08em;
        }

        .monogram b {
            color: var(--gold-light);
            font-weight: 400;
        }

        .ring-label {
            position: absolute;
            inset: 0;
            animation: rotate 32s linear infinite;
        }

        .ring-label span {
            position: absolute;
            top: -1px;
            left: 50%;
            color: #8b847b;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .13em;
            text-transform: uppercase;
            transform: translateX(-50%);
            background: var(--paper);
            padding: 0 14px;
        }

        .progress-wrap {
            width: min(100%, 570px);
            margin-top: clamp(30px, 4vw, 50px);
        }

        .progress-meta {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 11px;
            color: #8b847b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .progress {
            position: relative;
            height: 3px;
            overflow: hidden;
            border-radius: 10px;
            background: rgba(23, 23, 23, .11);
        }

        .progress::after {
            content: "";
            position: absolute;
            inset: 0;
            width: 58%;
            border-radius: inherit;
            background: linear-gradient(90deg, #ad7e39, var(--gold-light), #ad7e39);
            background-size: 200% 100%;
            animation: progress 3s ease-in-out infinite alternate, shimmer 2.4s linear infinite;
        }

        .footer {
            padding-top: 24px;
            border-top: 1px solid rgba(23, 23, 23, .1);
            color: #8c867e;
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .footer a {
            color: var(--ink);
            text-decoration: none;
            transition: color .2s ease;
        }

        .footer a:hover {
            color: var(--gold);
        }

        @keyframes pulse {
            0% { opacity: .8; transform: scale(.6); }
            70%, 100% { opacity: 0; transform: scale(1.8); }
        }

        @keyframes reveal {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes rotate {
            to { transform: rotate(360deg); }
        }

        @keyframes shine {
            0%, 20% { transform: rotate(-35deg) translateY(-190%); }
            55%, 100% { transform: rotate(-35deg) translateY(240%); }
        }

        @keyframes progress {
            from { width: 45%; }
            to { width: 78%; }
        }

        @keyframes shimmer {
            to { background-position: -200% 0; }
        }

        @media (max-width: 840px) {
            .page {
                gap: 12px;
            }

            .hero {
                grid-template-columns: minmax(0, 1fr);
                align-content: center;
                gap: 14px;
                padding: 46px 0 34px;
            }

            .content {
                text-align: center;
                margin: 0 auto;
            }

            .eyebrow {
                justify-content: center;
            }

            h1 span {
                margin-left: 0;
            }

            .intro,
            .progress-wrap {
                margin-left: auto;
                margin-right: auto;
            }

            .visual {
                order: -1;
                width: min(72vw, 360px);
                justify-self: center;
            }
        }

        @media (max-width: 560px) {
            .page {
                padding: 20px;
            }

            .topbar {
                align-items: flex-start;
            }

            .logo {
                width: 180px;
            }

            .status {
                width: 42px;
                height: 42px;
                justify-content: center;
                padding: 0;
            }

            .status-text {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0 0 0 0);
                white-space: nowrap;
            }

            .hero {
                padding-top: 24px;
            }

            .eyebrow {
                gap: 10px;
                font-size: 9px;
                letter-spacing: .14em;
            }

            .eyebrow::before {
                width: 24px;
                flex: 0 0 24px;
            }

            .visual {
                width: min(76vw, 300px);
            }

            h1 {
                font-size: clamp(46px, 15.5vw, 68px);
            }

            .progress-meta {
                gap: 8px;
                font-size: 8px;
                letter-spacing: .11em;
            }

            .footer {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>

<body>
    <main class="page">
        <header class="topbar">
            <svg class="logo" viewBox="0 0 480 126" role="img" aria-labelledby="logoTitle">
                <title id="logoTitle">Capital Holding KMB</title>
                <g aria-hidden="true">
                    <path fill="#c99b50" d="M0 6h48L0 54V6Zm0 68 48 46H0V74Z"/>
                    <path fill="#1d1d1d" d="M58 6v33L33 63l25 24v33L0 63 58 6Z"/>
                    <path fill="#1d1d1d" d="M66 6h82v114h-22V29H88v91H66V6Z"/>
                    <path fill="#c99b50" d="M96 37h22v83H96z"/>
                    <path fill="#1d1d1d" d="M156 6h59l-49 57 49 57h-59V6Z"/>
                    <path fill="#c99b50" d="m215 18-40 45 40 45V18Z"/>
                    <path fill="#c99b50" d="M438 0h22v22L438 0Z"/>

                    <g fill="#c99b50" transform="translate(239 5)">
                        <path d="M16 39C6 39 0 32 0 22S7 5 17 5c7 0 12 3 15 8l-7 4c-2-3-4-5-8-5-5 0-9 4-9 10 0 7 4 11 10 11 4 0 7-2 9-6l7 4c-3 5-9 8-18 8Z"/>
                        <path d="m47 6-14 33h8l3-8h15l3 8h9L57 6H47Zm0 18 5-12 5 12H47Z"/>
                        <path d="M74 6h14c9 0 14 4 14 11s-5 12-14 12h-6v10h-8V6Zm8 7v9h6c4 0 6-2 6-5 0-2-2-4-6-4h-6Z"/>
                        <path d="M107 6h8v33h-8zM121 6h29v7h-10v26h-8V13h-11V6ZM162 6h10l14 33h-9l-3-8h-15l-3 8h-8l14-33Zm0 18h9l-4-12-5 12ZM190 6h8v26h17v7h-25V6Z"/>
                        <path d="M0 49h8v13h15V49h8v33h-8V69H8v13H0V49ZM54 48c11 0 19 7 19 17s-8 18-19 18-19-7-19-18c0-10 8-17 19-17Zm0 7c-6 0-11 4-11 10s5 11 11 11 11-4 11-11c0-6-5-10-11-10ZM78 49h8v26h17v7H78V49ZM108 49h14c12 0 20 6 20 16s-8 17-20 17h-14V49Zm8 7v19h6c7 0 11-3 11-10 0-6-4-9-11-9h-6ZM147 49h8v33h-8zM162 49h8l16 20V49h8v33h-7l-17-20v20h-8V49ZM217 48c7 0 12 2 16 6l-5 5c-3-3-6-4-10-4-7 0-11 4-11 10 0 7 5 11 11 11 3 0 6-1 8-3v-5h-9v-6h17v15c-4 4-10 6-17 6-11 0-19-7-19-18 0-10 8-17 19-17Z"/>
                        <path d="M0 91h8v13l12-13h10l-14 15 15 18H20L8 110v14H0V91ZM33 91h11l9 21 9-21h11v33h-8v-22l-9 22h-6l-9-22v22h-8V91ZM80 91h16c8 0 12 3 12 8 0 4-2 6-5 7 4 1 7 4 7 8 0 6-5 10-14 10H80V91Zm8 6v7h7c3 0 5-1 5-4 0-2-2-3-5-3h-7Zm0 13v8h8c4 0 6-2 6-4 0-3-2-4-6-4h-8Z"/>
                    </g>
                </g>
            </svg>

            <div class="status" aria-label="Mise à jour en cours">
                <span class="status-dot" aria-hidden="true"></span>
                <span class="status-text">Mise à jour en cours</span>
            </div>
        </header>

        <section class="hero" aria-labelledby="main-title">
            <div class="content">
                <p class="eyebrow">Une nouvelle expérience arrive</p>
                <h1 id="main-title">Notre site <span>évolue.</span></h1>
                <p class="intro">
                    Nous mettons actuellement notre plateforme à jour afin de vous offrir
                    une expérience <strong>plus fluide, plus claire et plus performante.</strong>
                    Merci pour votre patience — nous revenons très bientôt.
                </p>

                <div class="progress-wrap" aria-label="Progression de la mise à jour">
                    <div class="progress-meta">
                        <span>Optimisation en cours</span>
                        <span>Patientez un instant</span>
                    </div>
                    <div class="progress" aria-hidden="true"></div>
                </div>
            </div>

            <div class="visual" aria-hidden="true">
                <div class="orbit">
                    <div class="ring-label"><span>Capital Holding · KMB</span></div>
                </div>
                <div class="orbit-inner"></div>
                <div class="core">
                    <div class="monogram"><b>K</b>MB</div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <span>© <?= date('Y') ?> Capital Holding KMB</span>
            <span>Excellence · Vision · Confiance</span>
        </footer>
    </main>
</body>
</html>
