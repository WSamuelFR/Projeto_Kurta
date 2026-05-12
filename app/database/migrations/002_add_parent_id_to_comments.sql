-- Migration 002: Adiciona suporte a respostas (comentários filhos)
ALTER TABLE coments ADD COLUMN parent_id INTEGER NULL REFERENCES coments(coment_id) ON DELETE CASCADE;
