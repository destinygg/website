if not ARGV[1] then
  return {err = "INVALID ARGUMENTS"}
end

local ip = ARGV[1]
local foundusers = {}
local keys = redis.call("KEYS", "CHAT:userips-*")
for _, key in ipairs(keys) do
  local ipfound = redis.call("ZRANK", key, ip)
  if ipfound then
    table.insert(foundusers, key)
  end
end

return foundusers
