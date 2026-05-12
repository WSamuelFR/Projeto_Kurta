import { PrismaClient } from '@prisma/client'
const prisma = new PrismaClient()

async function main() {
  const count = await prisma.feeling.count()
  console.log('Total feelings:', count)
  
  const sample = await prisma.feeling.findMany({ take: 5 })
  console.log('Sample feelings:', JSON.stringify(sample, null, 2))
}

main()
  .catch(e => console.error(e))
  .finally(async () => await prisma.$disconnect())
