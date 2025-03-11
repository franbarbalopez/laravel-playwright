async function e(t) {
  return await (await t.request.get("/__playwright__/csrf_token")).json();
}
async function a(t, n) {
  return await (await t.request.post("/__playwright__/factory", {
    data: n,
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": await e(t)
    }
  })).json();
}
async function o(t, n) {
  return await (await t.request.post("/__playwright__/login", {
    data: n || {},
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": await e(t)
    }
  })).json();
}
async function r(t) {
  await t.request.post("/__playwright__/logout", {
    headers: {
      "X-CSRF-TOKEN": await e(t)
    }
  });
}
async function i(t) {
  return await (await t.request.get("/__playwright__/user")).json();
}
export {
  a as factory,
  e as getCsrfToken,
  i as getUser,
  o as login,
  r as logout
};
