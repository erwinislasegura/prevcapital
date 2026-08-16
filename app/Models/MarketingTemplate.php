<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class MarketingTemplate
{
    public static function current(): array
    {
        $template = Database::connection()->query('SELECT * FROM marketing_templates ORDER BY id LIMIT 1')->fetch();
        if ($template) {
            return $template;
        }
        $html = '<!doctype html><html lang="es"><body style="margin:0;background:#eef3f5;font-family:Arial,sans-serif;color:#132530"><div style="max-width:640px;margin:24px auto;background:#ffffff;border-top:6px solid #00b9b5"><div style="padding:28px 34px;background:#071826;color:#ffffff"><h1 style="margin:0;font-size:24px">PREVCAPITAL</h1><p style="margin:7px 0 0;color:#9cdedb">Prevención que protege su operación</p></div><div style="padding:34px"><p style="margin:0 0 8px;color:#078b89;font-size:12px;font-weight:bold;letter-spacing:.08em">INFORMACIÓN PARA EMPRESAS</p><h2 style="margin:0 0 20px;color:#071826;font-size:24px">Hola, {{nombre}}</h2><div style="font-size:15px;line-height:1.75">{{contenido}}</div><p style="margin:30px 0"><a href="https://prevcapital.cl/contacto" style="display:inline-block;padding:14px 22px;background:#00b9b5;color:#071826;text-decoration:none;font-weight:bold">Solicitar una evaluación</a></p></div><div style="padding:20px 34px;background:#071826;color:#b9cbd1;font-size:12px;line-height:1.6">PrevCapital · La Serena, Chile<br><a href="mailto:contacto@prevcapital.cl" style="color:#9cdedb">contacto@prevcapital.cl</a><br><a href="{{unsubscribe_url}}" style="color:#9aaeb7">Dejar de recibir estos correos</a></div></div></body></html>';
        $text = "Hola, {{nombre}}\n\n{{contenido_texto}}\n\nPrevCapital · La Serena, Chile\nPara dejar de recibir estos correos: {{unsubscribe_url}}";
        $statement = Database::connection()->prepare('INSERT INTO marketing_templates (name, subject_default, html_content, text_content) VALUES (:name, :subject, :html, :text)');
        $statement->execute(['name' => 'Plantilla corporativa PrevCapital', 'subject' => '{{asunto}}', 'html' => $html, 'text' => $text]);
        return self::current();
    }

    public static function update(int $id, array $data, ?int $userId): void
    {
        $statement = Database::connection()->prepare('UPDATE marketing_templates SET name=:name, subject_default=:subject_default, html_content=:html_content, text_content=:text_content, updated_by=:updated_by WHERE id=:id');
        $statement->execute($data + ['updated_by' => $userId, 'id' => $id]);
    }
}
