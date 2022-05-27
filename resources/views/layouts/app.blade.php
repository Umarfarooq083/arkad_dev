<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link href="{{asset('public/favicon.ico')}}" rel="icon" />
    <meta content="minimum-scale=1,initial-scale=1,width=device-width,shrink-to-fit=no" name="viewport" />
    <meta content="#0A8FDC" name="theme-color" />
    <link href="{{asset('public/logo192.png')}}" rel="apple-touch-icon" />
    <meta content="React material admin template" name="description" />
    <meta property="og:title" content="Arkad Admin Panel" />
    <meta property="og:description" content="Arkad Admin Panel" />
    <meta property="og:image" content="{{asset('public/logo512.png')}}" />
    <link href="{{asset('public/assets/styles/index.css')}}" rel="stylesheet">
    <meta property="og:site_name" content="Arkad Admin Panel" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script async defer="defer"
            src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places,geometry,drawing&key=AIzaSyBtgCpqXBu7Mdl2bzhhHnutAroyEteQo9s"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap"
          rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <link href="{{asset('public/manifest.json')}} rel="manifest" />
    <title>Arkad</title>
    <style>
        body {
            margin: 0;
            padding: 0
        }

        .crema-loader-bg {
            flex: 1;
            display: flex;
            position: relative;
            height: 100vh;
            justify-content: center;
            align-items: center;
            background-size: cover
        }

        .crema-loader {
            position: relative;
            margin: 0 auto;
            width: 40px
        }

        .circular {
            animation: rotate 2s linear infinite;
            height: 40px;
            transform-origin: center center;
            width: 40px;
            margin: auto
        }

        .path {
            stroke-dasharray: 5, 200;
            stroke-dashoffset: 0;
            animation: dash 1.5s ease-in-out infinite, color 6s ease-in-out infinite;
            stroke-linecap: round
        }

        @keyframes rotate {
            100% {
                transform: rotate(360deg)
            }
        }

        @keyframes dash {
            0% {
                stroke-dasharray: 1, 200;
                stroke-dashoffset: 0
            }

            50% {
                stroke-dasharray: 89, 200;
                stroke-dashoffset: -35px
            }

            100% {
                stroke-dasharray: 89, 200;
                stroke-dashoffset: -124px
            }
        }

        @keyframes color {

            0%,
            100% {
                stroke: #0a8fdc
            }
        }
    </style>
    <link href="{{asset('public/static/css/6.67d634b6.chunk.css')}}" rel="stylesheet">
    <link href="{{asset('public/static/css/main.01f36c40.chunk.css')}}" rel="stylesheet">
</head>

<body><noscript>You need to enable JavaScript to run this app.</noscript>
<div id="root">
    <div class="crema-loader-bg">
        <div class="crema-loader"><svg class="circular" viewBox="25 25 50 50">
                <circle class="path" cx="50" cy="50" fill="none" r="20" stroke-miterlimit="10" stroke-width="3" />
            </svg></div>
    </div>
</div>
<script>!function (e) { function t(t) { for (var n, o, a = t[0], u = t[1], i = t[2], d = 0, s = []; d < a.length; d++)o = a[d], Object.prototype.hasOwnProperty.call(c, o) && c[o] && s.push(c[o][0]), c[o] = 0; for (n in u) Object.prototype.hasOwnProperty.call(u, n) && (e[n] = u[n]); for (l && l(t); s.length;)s.shift()(); return f.push.apply(f, i || []), r() } function r() { for (var e, t = 0; t < f.length; t++) { for (var r = f[t], n = !0, o = 1; o < r.length; o++) { var u = r[o]; 0 !== c[u] && (n = !1) } n && (f.splice(t--, 1), e = a(a.s = r[0])) } return e } var n = {}, o = { 5: 0 }, c = { 5: 0 }, f = []; function a(t) { if (n[t]) return n[t].exports; var r = n[t] = { i: t, l: !1, exports: {} }; return e[t].call(r.exports, r, r.exports, a), r.l = !0, r.exports } a.e = function (e) { var t = []; o[e] ? t.push(o[e]) : 0 !== o[e] && { 9: 1, 10: 1, 11: 1 }[e] && t.push(o[e] = new Promise((function (t, r) { for (var n = "static/css/" + ({}[e] || e) + "." + { 0: "31d6cfe0", 1: "31d6cfe0", 2: "31d6cfe0", 3: "31d6cfe0", 7: "31d6cfe0", 8: "31d6cfe0", 9: "11430080", 10: "8c5dea91", 11: "97332f18", 12: "31d6cfe0", 13: "31d6cfe0", 14: "31d6cfe0", 15: "31d6cfe0", 16: "31d6cfe0", 17: "31d6cfe0", 18: "31d6cfe0", 19: "31d6cfe0", 20: "31d6cfe0", 21: "31d6cfe0", 22: "31d6cfe0", 23: "31d6cfe0", 24: "31d6cfe0" }[e] + ".chunk.css", c = a.p + n, f = document.getElementsByTagName("link"), u = 0; u < f.length; u++) { var i = (l = f[u]).getAttribute("data-href") || l.getAttribute("href"); if ("stylesheet" === l.rel && (i === n || i === c)) return t() } var d = document.getElementsByTagName("style"); for (u = 0; u < d.length; u++) { var l; if ((i = (l = d[u]).getAttribute("data-href")) === n || i === c) return t() } var s = document.createElement("link"); s.rel = "stylesheet", s.type = "text/css", s.onload = t, s.onerror = function (t) { var n = t && t.target && t.target.src || c, f = new Error("Loading CSS chunk " + e + " failed.\n(" + n + ")"); f.code = "CSS_CHUNK_LOAD_FAILED", f.request = n, delete o[e], s.parentNode.removeChild(s), r(f) }, s.href = c, document.getElementsByTagName("head")[0].appendChild(s) })).then((function () { o[e] = 0 }))); var r = c[e]; if (0 !== r) if (r) t.push(r[2]); else { var n = new Promise((function (t, n) { r = c[e] = [t, n] })); t.push(r[2] = n); var f, u = document.createElement("script"); u.charset = "utf-8", u.timeout = 120, a.nc && u.setAttribute("nonce", a.nc), u.src = function (e) { return a.p + "static/js/" + ({}[e] || e) + "." + { 0: "9fe63f1b", 1: "18945414", 2: "e4c86cfc", 3: "55ff7fdb", 7: "ad017cbf", 8: "6181baab", 9: "c7061352", 10: "fd6c11e3", 11: "76aaa04b", 12: "1d630bfe", 13: "66951f1f", 14: "71614329", 15: "cd2c5695", 16: "9b5f3609", 17: "841f1532", 18: "50930fff", 19: "ae4de291", 20: "ff4fc3dd", 21: "295f9e08", 22: "b2ba28ec", 23: "be81b1f9", 24: "b1e8eec3" }[e] + ".chunk.js" }(e); var i = new Error; f = function (t) { u.onerror = u.onload = null, clearTimeout(d); var r = c[e]; if (0 !== r) { if (r) { var n = t && ("load" === t.type ? "missing" : t.type), o = t && t.target && t.target.src; i.message = "Loading chunk " + e + " failed.\n(" + n + ": " + o + ")", i.name = "ChunkLoadError", i.type = n, i.request = o, r[1](i) } c[e] = void 0 } }; var d = setTimeout((function () { f({ type: "timeout", target: u }) }), 12e4); u.onerror = u.onload = f, document.head.appendChild(u) } return Promise.all(t) }, a.m = e, a.c = n, a.d = function (e, t, r) { a.o(e, t) || Object.defineProperty(e, t, { enumerable: !0, get: r }) }, a.r = function (e) { "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, { value: "Module" }), Object.defineProperty(e, "__esModule", { value: !0 }) }, a.t = function (e, t) { if (1 & t && (e = a(e)), 8 & t) return e; if (4 & t && "object" == typeof e && e && e.__esModule) return e; var r = Object.create(null); if (a.r(r), Object.defineProperty(r, "default", { enumerable: !0, value: e }), 2 & t && "string" != typeof e) for (var n in e) a.d(r, n, function (t) { return e[t] }.bind(null, n)); return r }, a.n = function (e) { var t = e && e.__esModule ? function () { return e.default } : function () { return e }; return a.d(t, "a", t), t }, a.o = function (e, t) { return Object.prototype.hasOwnProperty.call(e, t) }, a.p = "./", a.oe = function (e) { throw console.error(e), e }; var u = this.webpackJsonphister = this.webpackJsonphister || [], i = u.push.bind(u); u.push = t, u = u.slice(); for (var d = 0; d < u.length; d++)t(u[d]); var l = i; r() }([])</script>
<script src="{{asset('public/static/js/6.11df56c8.chunk.js')}}"></script>
<script src="{{asset('public/static/js/main.72a313f5.chunk.js')}}"></script>
</body>

</html>
