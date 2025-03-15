async function e({ page: t }) {
  return await (await t.request.get("/__playwright__/csrf_token", { headers: { Accept: "application/json" } })).json();
}
async function a({ page: t, options: n }) {
  const o = await e({ page: t });
  return await (await t.request.post("/__playwright__/factory", {
    headers: { Accept: "application/json" },
    data: {
      _token: o,
      options: n
    }
  })).json();
}
async function r({ page: t, options: n }) {
  const o = await e({ page: t });
  return await (await t.request.post("/__playwright__/login", {
    headers: { Accept: "application/json" },
    data: {
      _token: o,
      options: n || {}
    }
  })).json();
}
async function c({ page: t }) {
  const n = await e({ page: t });
  await t.request.post("/__playwright__/logout", {
    headers: { Accept: "application/json" },
    data: { _token: n }
  });
}
async function i({ page: t }) {
  return await (await t.request.get("/__playwright__/user", { headers: { Accept: "application/json" } })).json();
}
export {
  e as csrfToken,
  a as factory,
  r as login,
  c as logout,
  i as user
};
