import otpGenerator from "otp-generator";

function readArg(name, fallback = null) {
  const idx = process.argv.indexOf(`--${name}`);
  if (idx === -1) return fallback;
  const value = process.argv[idx + 1];
  return value ?? fallback;
}

const lengthRaw = readArg("length", "6");
const length = Math.max(1, Number.parseInt(lengthRaw, 10) || 6);

// Digits-only OTP for compatibility with existing Laravel validation rules.
const otp = otpGenerator.generate(length, {
  upperCaseAlphabets: false,
  lowerCaseAlphabets: false,
  specialChars: false,
});

process.stdout.write(String(otp));
