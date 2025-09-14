export const eur = (v:number) => v.toLocaleString('fr-FR', { style:'currency', currency:'EUR' })
export const pct = (v:number) => `${v.toLocaleString('fr-FR', { maximumFractionDigits:2 })} %`
