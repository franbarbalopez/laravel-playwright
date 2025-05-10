const n = {
  Accept: "application/json"
};
async function a(t) {
  return (await t.request.get("/__playwright__/csrf_token", {
    headers: n
  })).json();
}
async function c(t, s) {
  const e = await a(t);
  return (await t.request.post("/__playwright__/factory", {
    headers: { ...n, "X-CSRF-TOKEN": e },
    data: s
  })).json();
}
async function i(t, s) {
  const e = await a(t);
  return (await t.request.post("/__playwright__/login", {
    headers: { ...n, "X-CSRF-TOKEN": e },
    data: s || {}
  })).json();
}
async function _(t) {
  const s = await a(t);
  await t.request.post("/__playwright__/logout", {
    headers: { ...n, "X-CSRF-TOKEN": s }
  });
}
async function u(t) {
  return (await t.request.get("/__playwright__/user", {
    headers: n
  })).json();
}
async function r(t, s, e = {}) {
  const o = await a(t);
  await t.request.post("/__playwright__/artisan", {
    headers: { ...n, "X-CSRF-TOKEN": o },
    data: { command: s, parameters: e }
  });
}
async function p(t, s = {}) {
  return await r(t, "migrate:fresh", s);
}
async function w(t, s = "") {
  const e = {};
  return s && (e["--class"] = s), await r(t, "db:seed", e);
}
export {
  r as artisan,
  a as csrfToken,
  c as factory,
  i as login,
  _ as logout,
  p as refreshDatabase,
  w as seedDatabase,
  u as user
};
