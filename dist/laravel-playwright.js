const e = {
  Accept: "application/json"
};
async function o(s) {
  return (await s.request.get("/__playwright__/csrf_token", {
    headers: e
  })).json();
}
async function a(s, t) {
  const n = await o(s);
  return (await s.request.post("/__playwright__/factory", {
    headers: { ...e, "X-CSRF-TOKEN": n },
    ...t
  })).json();
}
async function c(s, t) {
  const n = await o(s);
  return (await s.request.post("/__playwright__/login", {
    headers: { ...e, "X-CSRF-TOKEN": n },
    ...t || {}
  })).json();
}
async function i(s) {
  const t = await o(s);
  await s.request.post("/__playwright__/logout", {
    headers: { ...e, "X-CSRF-TOKEN": t }
  });
}
async function _(s) {
  return (await s.request.get("/__playwright__/user", {
    headers: e
  })).json();
}
export {
  o as csrfToken,
  a as factory,
  c as login,
  i as logout,
  _ as user
};
