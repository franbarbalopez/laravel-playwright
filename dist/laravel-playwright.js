const e = {
  Accept: "application/json"
};
async function o(t) {
  return (await t.request.get("/__playwright__/csrf_token", {
    headers: e
  })).json();
}
async function a(t, s) {
  const n = await o(t);
  return (await t.request.post("/__playwright__/factory", {
    headers: { ...e, "X-CSRF-TOKEN": n },
    data: s
  })).json();
}
async function c(t, s) {
  const n = await o(t);
  return (await t.request.post("/__playwright__/login", {
    headers: { ...e, "X-CSRF-TOKEN": n },
    data: s || {}
  })).json();
}
async function i(t) {
  const s = await o(t);
  await t.request.post("/__playwright__/logout", {
    headers: { ...e, "X-CSRF-TOKEN": s }
  });
}
async function _(t) {
  return (await t.request.get("/__playwright__/user", {
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
