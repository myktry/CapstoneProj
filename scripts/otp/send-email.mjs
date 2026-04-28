import nodemailer from "nodemailer";

function readArg(name, fallback = null) {
  const idx = process.argv.indexOf(`--${name}`);
  if (idx === -1) return fallback;
  const value = process.argv[idx + 1];
  return value ?? fallback;
}

const to = readArg("to");
const code = readArg("code");
const purpose = readArg("purpose", "verification");
const expires = readArg("expires", "5");

if (!to) throw new Error("Missing required argument: --to");
if (!code) throw new Error("Missing required argument: --code");

const user = process.env.EMAIL_USER;
const pass = process.env.EMAIL_PASS;

if (!user) throw new Error("Missing env: EMAIL_USER");
if (!pass) throw new Error("Missing env: EMAIL_PASS");

const transporter = nodemailer.createTransport({
  service: "gmail",
  auth: { user, pass },
});

const subject = "Your Black Ember verification code";
const text = `Your Black Ember verification code is ${code}. It expires in ${expires} minutes.`;
const html = `
  <div style="font-family: Arial, sans-serif; line-height: 1.4;">
    <h2 style="margin: 0 0 8px;">Black Ember verification code</h2>
    <p style="margin: 0 0 12px;">Use this code to complete <strong>${purpose}</strong>:</p>
    <div style="font-size: 28px; letter-spacing: 6px; font-weight: 700; margin: 10px 0 14px;">
      ${code}
    </div>
    <p style="margin: 0; color: #555;">This code expires in ${expires} minutes.</p>
  </div>
`.trim();

await transporter.sendMail({
  from: user,
  to,
  subject,
  text,
  html,
});

process.stdout.write("OK");
