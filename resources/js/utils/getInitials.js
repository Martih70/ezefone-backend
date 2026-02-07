export function getInitials(name) {
  if (!name || typeof name !== 'string') {
    return '';
  }

  return name
    .trim()
    .split(' ')
    .map(part => part[0])
    .filter(Boolean)
    .join('')
    .toUpperCase()
    .substring(0, 2);
}
