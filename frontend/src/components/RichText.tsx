import { createElement } from "react";
import type { BlockChild, BlockNode } from "@/lib/strapi";

function wrapMarks(text: string, child: BlockChild) {
  let node: React.ReactNode = text;
  if (child.code) {
    node = (
      <code className="rounded bg-stone-100 px-1 py-0.5 font-mono text-sm text-stone-800">
        {node}
      </code>
    );
  }
  if (child.bold) node = <strong>{node}</strong>;
  if (child.italic) node = <em>{node}</em>;
  if (child.underline) node = <u>{node}</u>;
  if (child.strikethrough) node = <s>{node}</s>;
  return node;
}

function renderInline(children: BlockChild[] | undefined, keyPrefix: string) {
  if (!children?.length) return null;
  return children.map((child, i) => {
    const k = `${keyPrefix}-${i}`;
    if (child.type === "text" && child.text != null) {
      return <span key={k}>{wrapMarks(child.text, child)}</span>;
    }
    if (child.type === "link" && child.children) {
      const href = (child as unknown as { url?: string }).url ?? "#";
      return (
        <a
          key={k}
          href={href}
          className="text-emerald-700 underline underline-offset-2 hover:text-emerald-900"
          target={href.startsWith("http") ? "_blank" : undefined}
          rel={href.startsWith("http") ? "noopener noreferrer" : undefined}
        >
          {renderInline(child.children, k)}
        </a>
      );
    }
    return null;
  });
}

function Heading({
  level,
  className,
  children,
}: {
  level: number;
  className: string;
  children: React.ReactNode;
}) {
  const L = Math.min(Math.max(level, 1), 6);
  return createElement(`h${L}`, { className }, children);
}

function Block({ block, i }: { block: BlockNode; i: number }) {
  const key = `b-${i}`;
  switch (block.type) {
    case "paragraph":
      return (
        <p key={key} className="mb-4 leading-relaxed text-stone-700 last:mb-0">
          {renderInline(block.children, key)}
        </p>
      );
    case "heading": {
      const level = block.level ?? 2;
      const cls =
        level <= 2
          ? "mb-3 mt-8 font-serif text-2xl text-stone-900"
          : "mb-2 mt-6 font-serif text-xl text-stone-900";
      return (
        <Heading key={key} level={level} className={cls}>
          {renderInline(block.children, key)}
        </Heading>
      );
    }
    case "list": {
      const isOrdered = block.format === "ordered";
      const ListTag = isOrdered ? "ol" : "ul";
      return (
        <ListTag
          key={key}
          className={`mb-4 ml-6 space-y-2 text-stone-700 ${
            isOrdered ? "list-decimal" : "list-disc"
          }`}
        >
          {block.children?.map((item, j) => {
            if (item.type !== "list-item") return null;
            return (
              <li key={`${key}-li-${j}`}>
                {renderInline(item.children, `${key}-li-${j}`)}
              </li>
            );
          })}
        </ListTag>
      );
    }
    case "quote":
      return (
        <blockquote
          key={key}
          className="my-4 border-l-4 border-emerald-600/40 pl-4 italic text-stone-600"
        >
          {renderInline(block.children, key)}
        </blockquote>
      );
    case "code":
      return (
        <pre
          key={key}
          className="mb-4 overflow-x-auto rounded-lg bg-stone-100 p-4 text-sm"
        >
          <code>{renderInline(block.children, key)}</code>
        </pre>
      );
    default:
      return null;
  }
}

export function RichText({ content }: { content: unknown }) {
  if (content == null) return null;
  if (typeof content === "string") {
    return <p className="text-stone-700">{content}</p>;
  }
  if (!Array.isArray(content)) return null;
  return (
    <div className="max-w-none">
      {(content as BlockNode[]).map((block, i) => (
        <Block key={i} block={block} i={i} />
      ))}
    </div>
  );
}
