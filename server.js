const http = require("http");
const https = require("https");
const tls = require("tls");
const fs = require("fs");
const path = require("path");

const root = __dirname;
const port = Number(process.env.PORT || 3000);

loadEnvFile();

const mimeTypes = {
  ".html": "text/html; charset=utf-8",
  ".php": "text/html; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".js": "application/javascript; charset=utf-8",
  ".png": "image/png",
  ".jpg": "image/jpeg",
  ".jpeg": "image/jpeg",
  ".webp": "image/webp",
  ".gif": "image/gif",
  ".svg": "image/svg+xml",
  ".mp4": "video/mp4",
  ".webm": "video/webm",
  ".mov": "video/quicktime",
  ".ico": "image/x-icon",
  ".json": "application/json; charset=utf-8",
  ".xml": "application/xml; charset=utf-8",
  ".txt": "text/plain; charset=utf-8"
};

const companyContext = `
Capital Holding KMB is a multisector holding based in Rabat, Morocco.
Head office: 243 Rue Zouheir Ben Qaiss, Rabat, Maroc.
International presence: Dubai, UAE.
Email: capitalholding.kmb1@gmail.com.
Fixed phone: 0537 751412.
WhatsApp: 0661868619.
Sectors: construction and infrastructures; Petroleum covering renewable energy plus energy and hydrocarbons; agriculture and agrifood; transport and logistics; precious metals and gold.
Operational entities mentioned on the website: Haryam, Petroleum, Bio Agri Hamza, Haryam Dubai, Ylartours and Elkhaldi.
Bio Agri Hamza combines certified frozen meat imports (beef, poultry and lamb), integrated fruit and vegetable production, refrigerated logistics, full traceability, Halal compliance, HACCP hygiene procedures and regulatory veterinary controls.
Mission: catalyze economic growth through investment in high-value sectors, from African markets to global hubs like Dubai.
Only answer using these facts. If a detail is not available, say that it is not available on the website.
`;

const server = http.createServer(async (req, res) => {
  try {
    if (req.method === "POST" && req.url === "/api/chat") {
      await handleChat(req, res);
      return;
    }

    if (req.method === "POST" && req.url === "/api/contact") {
      await handleContact(req, res);
      return;
    }

    if (req.method !== "GET" && req.method !== "HEAD") {
      sendJson(res, 405, { error: "Method not allowed" });
      return;
    }

    serveStatic(req, res);
  } catch (error) {
    sendJson(res, 500, { error: "Server error" });
  }
});

server.listen(port, () => {
  console.log(`Capital Holding KMB site: http://localhost:${port}`);
});

function loadEnvFile() {
  const envPath = path.join(root, ".env");
  if (!fs.existsSync(envPath)) return;

  const lines = fs.readFileSync(envPath, "utf8").split(/\r?\n/);
  for (const line of lines) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith("#")) continue;
    const index = trimmed.indexOf("=");
    if (index === -1) continue;
    const key = trimmed.slice(0, index).trim();
    const value = trimmed.slice(index + 1).trim().replace(/^["']|["']$/g, "");
    if (key && !(key in process.env)) process.env[key] = value;
  }
}

function serveStatic(req, res) {
  const url = new URL(req.url, `http://${req.headers.host}`);
  const requested = decodeURIComponent(url.pathname === "/" ? "/index.php" : url.pathname);
  const filePath = path.resolve(root, `.${requested}`);

  if (!filePath.startsWith(root)) {
    sendJson(res, 403, { error: "Forbidden" });
    return;
  }

  fs.readFile(filePath, (error, data) => {
    if (error) {
      sendJson(res, 404, { error: "Not found" });
      return;
    }

    const type = mimeTypes[path.extname(filePath).toLowerCase()] || "application/octet-stream";
    res.writeHead(200, { "Content-Type": type });
    if (req.method === "HEAD") res.end();
    else res.end(data);
  });
}

async function handleChat(req, res) {
  const apiKey = process.env.OPENROUTER_API_KEY;
  if (!apiKey) {
    sendJson(res, 503, { error: "OPENROUTER_API_KEY is not configured" });
    return;
  }

  const body = await readJsonBody(req);
  const message = String(body.message || "").trim();
  const language = ["fr", "en", "ar"].includes(body.language) ? body.language : "fr";

  if (!message) {
    sendJson(res, 400, { error: "Message is required" });
    return;
  }

  const languageNames = { fr: "French", en: "English", ar: "Arabic" };
  const payload = {
    model: process.env.OPENROUTER_MODEL || "openai/gpt-4o-mini",
    messages: [
      {
        role: "system",
        content: `${companyContext}\nAnswer in ${languageNames[language]}. Keep the answer concise, professional and useful.`
      },
      { role: "user", content: message }
    ],
    temperature: 0.2,
    max_tokens: 280
  };

  const data = await postJson("https://openrouter.ai/api/v1/chat/completions", payload, {
    Authorization: `Bearer ${apiKey}`,
    "HTTP-Referer": "http://localhost",
    "X-Title": "Capital Holding KMB",
    "Content-Type": "application/json"
  });

  const answer = data?.choices?.[0]?.message?.content?.trim();
  if (!answer) {
    sendJson(res, 502, { error: "Empty model response" });
    return;
  }

  sendJson(res, 200, { answer });
}

async function handleContact(req, res) {
  const body = await readJsonBody(req);
  const name = cleanText(body.name, 120);
  const email = cleanEmail(body.email);
  const phone = cleanText(body.phone, 60);
  const subject = cleanText(body.subject, 180);
  const message = cleanText(body.message, 4000);

  if (!name || !email || !phone || !subject || !message) {
    sendJson(res, 400, { error: "All contact fields are required" });
    return;
  }

  const smtpHost = process.env.SMTP_HOST || "smtp.gmail.com";
  const smtpPort = Number(process.env.SMTP_PORT || 465);
  const smtpUser = process.env.SMTP_USER || "capitalholding.kmb1@gmail.com";
  const smtpPass = process.env.SMTP_PASS;
  const from = process.env.CONTACT_FROM || smtpUser;
  const to = process.env.CONTACT_TO || "capitalholding.kmb1@gmail.com";

  if (!smtpPass) {
    sendJson(res, 503, { error: "SMTP_PASS is not configured" });
    return;
  }

  const text = [
    "Nouveau message depuis le site Capital Holding KMB",
    "",
    `Nom: ${name}`,
    `Email: ${email}`,
    `Telephone: ${phone}`,
    "",
    "Message:",
    message
  ].join("\n");

  const raw = [
    `From: ${formatAddress("Capital Holding KMB", from)}`,
    `To: ${formatAddress("Capital Holding KMB", to)}`,
    `Reply-To: ${formatAddress(name, email)}`,
    `Subject: ${encodeHeader(subject)}`,
    "MIME-Version: 1.0",
    "Content-Type: text/plain; charset=UTF-8",
    "Content-Transfer-Encoding: 8bit",
    "",
    text
  ].join("\r\n");

  await sendSmtpMail({
    host: smtpHost,
    port: smtpPort,
    user: smtpUser,
    pass: smtpPass,
    from,
    to,
    raw
  });

  sendJson(res, 200, { ok: true });
}

function readJsonBody(req) {
  return new Promise((resolve, reject) => {
    let raw = "";
    req.on("data", chunk => {
      raw += chunk;
      if (raw.length > 16_000) {
        req.destroy();
        reject(new Error("Body too large"));
      }
    });
    req.on("end", () => {
      try {
        resolve(raw ? JSON.parse(raw) : {});
      } catch (error) {
        reject(error);
      }
    });
    req.on("error", reject);
  });
}

function postJson(url, payload, headers) {
  return new Promise((resolve, reject) => {
    const target = new URL(url);
    const body = JSON.stringify(payload);
    const request = https.request({
      hostname: target.hostname,
      path: target.pathname,
      method: "POST",
      headers: {
        ...headers,
        "Content-Length": Buffer.byteLength(body)
      }
    }, response => {
      let raw = "";
      response.on("data", chunk => raw += chunk);
      response.on("end", () => {
        let parsed;
        try {
          parsed = raw ? JSON.parse(raw) : {};
        } catch (error) {
          reject(error);
          return;
        }

        if (response.statusCode < 200 || response.statusCode >= 300) {
          reject(new Error(parsed.error?.message || `OpenRouter error ${response.statusCode}`));
          return;
        }

        resolve(parsed);
      });
    });

    request.on("error", reject);
    request.write(body);
    request.end();
  });
}

function sendSmtpMail({ host, port, user, pass, from, to, raw }) {
  return new Promise((resolve, reject) => {
    const socket = tls.connect({ host, port, servername: host });
    socket.setEncoding("utf8");
    socket.setTimeout(20_000);

    let buffer = "";
    let waiter;

    const cleanup = () => {
      socket.removeAllListeners("data");
      socket.removeAllListeners("error");
      socket.removeAllListeners("timeout");
    };

    const fail = error => {
      cleanup();
      socket.destroy();
      reject(error);
    };

    const readResponse = () => new Promise((responseResolve, responseReject) => {
      waiter = { resolve: responseResolve, reject: responseReject };
      flushResponse();
    });

    const flushResponse = () => {
      if (!waiter) return;
      const response = extractSmtpResponse(buffer);
      if (!response) return;
      buffer = "";
      const current = waiter;
      waiter = null;
      current.resolve(response);
    };

    const command = async (line, expected, label) => {
      socket.write(`${line}\r\n`);
      const response = await readResponse();
      assertSmtpCode(response, expected, label);
      return response;
    };

    socket.on("data", chunk => {
      buffer += chunk;
      flushResponse();
    });

    socket.on("error", fail);
    socket.on("timeout", () => fail(new Error("SMTP timeout")));

    socket.on("secureConnect", async () => {
      try {
        assertSmtpCode(await readResponse(), [220], "SMTP greeting");
        await command("EHLO capitalholding-kmb.ma", [250], "EHLO");
        await command("AUTH LOGIN", [334], "AUTH LOGIN");
        await command(Buffer.from(user, "utf8").toString("base64"), [334], "SMTP username");
        await command(Buffer.from(pass, "utf8").toString("base64"), [235], "SMTP password");
        await command(`MAIL FROM:<${from}>`, [250], "MAIL FROM");
        await command(`RCPT TO:<${to}>`, [250, 251], "RCPT TO");
        await command("DATA", [354], "DATA");
        socket.write(`${dotStuff(raw)}\r\n.\r\n`);
        assertSmtpCode(await readResponse(), [250], "message body");
        await command("QUIT", [221, 250], "QUIT");
        cleanup();
        socket.end();
        resolve();
      } catch (error) {
        fail(error);
      }
    });
  });
}

function extractSmtpResponse(buffer) {
  if (!buffer.endsWith("\n")) return null;
  const lines = buffer.trimEnd().split(/\r?\n/);
  if (!lines.length) return null;
  const last = lines[lines.length - 1];
  if (!/^\d{3} /.test(last)) return null;
  return lines.join("\n");
}

function assertSmtpCode(response, expected, label) {
  const code = Number(response.slice(0, 3));
  if (!expected.includes(code)) {
    throw new Error(`${label} failed: ${response}`);
  }
}

function dotStuff(text) {
  return String(text).replace(/\r?\n/g, "\r\n").replace(/^\./gm, "..");
}

function cleanText(value, maxLength) {
  return String(value || "").replace(/\0/g, "").trim().slice(0, maxLength);
}

function cleanEmail(value) {
  const email = cleanText(value, 254).replace(/[\r\n<>]/g, "");
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) ? email : "";
}

function cleanHeader(value) {
  return cleanText(value, 180).replace(/[\r\n]/g, " ");
}

function encodeHeader(value) {
  return `=?UTF-8?B?${Buffer.from(cleanHeader(value), "utf8").toString("base64")}?=`;
}

function formatAddress(name, email) {
  return `${encodeHeader(name)} <${cleanEmail(email) || cleanHeader(email)}>`;
}

function sendJson(res, status, data) {
  res.writeHead(status, { "Content-Type": "application/json; charset=utf-8" });
  res.end(JSON.stringify(data));
}

