export const setAccessToken = (token) =>
  localStorage.setItem("access_token", token);

export const getAccessToken = () => localStorage.getItem("access_token");

export const setRefreshToken = (token) =>
  localStorage.setItem("refresh_token", token);

export const getRefreshToken = () => localStorage.getItem("refresh_token");

export const storeUser = (user) =>
  localStorage.setItem("user", JSON.stringify(user));

export const fetchUser = () => fetchObjectFromLocalStorage("user");

export const clearUser = () => {
  localStorage.removeItem("access_token");
  localStorage.removeItem("refresh_token");
  localStorage.removeItem("user");
};

const fetchObjectFromLocalStorage = (key) => {
  const value = localStorage.getItem(key);
  return value && JSON.parse(value);
};
