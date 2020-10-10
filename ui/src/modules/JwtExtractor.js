import jwt from "jsonwebtoken";

export const decodedToken = (token) => jwt.decode(token);
export const isTokenValid = (token) => {
  const dt = decodedToken(token);
  if (!dt) return false;
  const tokenExpirationDateTime = new Date(dt.exp * 1000);
  const currentDateTime = new Date();
  return tokenExpirationDateTime >= currentDateTime;
};
