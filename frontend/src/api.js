// api.js
// Small helper functions for talking to the PHP API.

const API_BASE =
  window.location.hostname === "localhost"
    ? "http://localhost:8000/api/"
    : "/api/"

async function apiRequest(endpoint, options = {}) {
  const response = await fetch(API_BASE + endpoint, {
    credentials: "include",
    ...options,
    headers: {
      "Content-Type": "application/json",
      ...(options.headers || {}),
    },
  })

  const text = await response.text()

  let data

  try {
    data = text ? JSON.parse(text) : {}
  } catch (error) {
    throw new Error("The server did not return valid JSON.")
  }

  if (!response.ok || data.success === false) {
    throw new Error(data.message || "Request failed.")
  }

  return data
}

export function apiGet(endpoint) {
  return apiRequest(endpoint)
}

export function apiPost(endpoint, body = {}) {
  return apiRequest(endpoint, {
    method: "POST",
    body: JSON.stringify(body),
  })
}